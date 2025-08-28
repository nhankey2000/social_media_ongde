<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\YouTubeVideo;
use Google_Client;
use Google_Service_YouTube;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UploadYouTubeVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 phút timeout
    public $tries = 3; // Thử lại tối đa 3 lần
    public $maxExceptions = 3; // Số exception tối đa
    public $backoff = [60, 300, 600]; // Delay giữa các lần retry: 1min, 5min, 10min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public YouTubeVideo $video
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Set PHP timeout
        set_time_limit(1800);

        try {
            Log::info('=== YouTube Upload Job Started ===', [
                'video_id' => $this->video->id,
                'title' => $this->video->title,
                'video_type' => $this->video->video_type ?? 'long',
                'attempt' => $this->attempts(),
                'memory_start' => memory_get_usage(true) / 1024 / 1024 . 'MB'
            ]);

            // Refresh video để đảm bảo dữ liệu mới nhất
            $this->video->refresh();

            // Kiểm tra video vẫn tồn tại
            if (!$this->video->exists) {
                throw new \Exception('Video không tồn tại trong database');
            }

            // Cập nhật trạng thái đang upload
            $this->video->markAsUploading();
            Log::info('Status updated to uploading');

            // Kiểm tra platform account
            $platformAccount = $this->video->platformAccount;
            if (!$platformAccount) {
                throw new \Exception('Không tìm thấy kênh YouTube.');
            }
            Log::info('Platform account validated', ['account_id' => $platformAccount->id]);

            // Kiểm tra file video
            if (!$this->video->hasValidFile()) {
                throw new \Exception('File video không tồn tại: ' . ($this->video->video_file ?? 'null'));
            }

            $videoPath = $this->video->getFilePath();
            $fileSize = $this->video->getFileSize();
            $fileSizeMB = $this->video->getFileSizeMB();

            Log::info('Video file validated', [
                'path' => $videoPath,
                'size_mb' => $fileSizeMB,
                'exists' => file_exists($videoPath)
            ]);

            // Kiểm tra kích thước file
            if ($fileSizeMB > 1024) { // > 1GB
                Log::warning('Large file detected', ['size_mb' => $fileSizeMB]);
            }

            // Initialize Google Client
            Log::info('Initializing Google Client...');
            $client = new Google_Client();

            // Decode và validate token
            $tokenData = json_decode($platformAccount->access_token, true);
            if (!$tokenData || !is_array($tokenData)) {
                throw new \Exception('Invalid access token format');
            }

            $client->setAccessToken($tokenData);
            Log::info('Access token set successfully');

            // Handle token refresh
            if ($client->isAccessTokenExpired()) {
                Log::info('Access token expired, attempting refresh...');

                $facebookAccount = DB::table('facebook_accounts')
                    ->where('platform_id', 3)
                    ->first();

                if (!$facebookAccount) {
                    throw new \Exception('Không tìm thấy thông tin ứng dụng YouTube.');
                }

                $client->setClientId($facebookAccount->app_id);
                $client->setClientSecret($facebookAccount->app_secret);
                $client->setRedirectUri($facebookAccount->redirect_url);

                $refreshToken = $client->getRefreshToken();
                if (!$refreshToken) {
                    throw new \Exception('No refresh token available. Re-authentication required.');
                }

                try {
                    $client->refreshToken($refreshToken);
                    $newToken = $client->getAccessToken();

                    if (!$newToken) {
                        throw new \Exception('Token refresh returned null');
                    }

                    $platformAccount->update(['access_token' => json_encode($newToken)]);
                    Log::info('Token refreshed successfully');
                } catch (\Exception $e) {
                    Log::error('Token refresh failed', ['error' => $e->getMessage()]);
                    throw new \Exception('Token refresh failed: ' . $e->getMessage());
                }
            } else {
                Log::info('Access token is still valid');
            }

            // Create YouTube service and test connection
            Log::info('Creating YouTube service...');
            $youtube = new Google_Service_YouTube($client);

            try {
                $channels = $youtube->channels->listChannels('snippet', ['mine' => true]);
                Log::info('YouTube API connection successful', ['channels_count' => count($channels->getItems())]);
            } catch (\Exception $e) {
                Log::error('YouTube API connection failed', ['error' => $e->getMessage()]);
                throw new \Exception('YouTube API connection failed: ' . $e->getMessage());
            }

            // Create video metadata
            Log::info('Creating video metadata...');
            $videoObj = new \Google_Service_YouTube_Video();
            $snippet = new \Google_Service_YouTube_VideoSnippet();

            // Set title
            $optimizedTitle = $this->video->getOptimizedTitle();
            $snippet->setTitle($optimizedTitle);
            Log::info('Title set', ['title' => $optimizedTitle]);

            // Set description
            $optimizedDescription = $this->video->getOptimizedDescription();
            $snippet->setDescription($optimizedDescription);

            // Set category - Fixed method call
            $categoryId = $this->video->getOptimizedCategoryId();
            $snippet->setCategoryId($categoryId);

            // Set tags
            $tags = $this->video->getAutoTags();
            if (!empty($tags)) {
                $snippet->setTags($tags);
                Log::info('Tags set', ['tags_count' => count($tags), 'tags' => $tags]);
            }

            $videoObj->setSnippet($snippet);

            // Set status
            $status = new \Google_Service_YouTube_VideoStatus();
            $status->setPrivacyStatus($this->video->status);
            $videoObj->setStatus($status);

            Log::info('Video metadata configured', [
                'video_type' => $this->video->video_type,
                'category' => $categoryId,
                'status' => $this->video->status
            ]);

            // Start upload process
            Log::info('Starting upload process...', [
                'file_size_mb' => $fileSizeMB,
                'memory_before_upload' => memory_get_usage(true) / 1024 / 1024 . 'MB'
            ]);

            // Configure upload with appropriate chunk size based on file size
            $chunkSizeBytes = $this->getOptimalChunkSize($fileSize);
            $totalChunks = ceil($fileSize / $chunkSizeBytes);

            $client->setDefer(true);
            $insertRequest = $youtube->videos->insert('snippet,status', $videoObj);

            $media = new \Google_Http_MediaFileUpload(
                $client,
                $insertRequest,
                'video/*',
                null,
                true,
                $chunkSizeBytes
            );
            $media->setFileSize($fileSize);

            Log::info('Upload initialized', [
                'chunk_size_mb' => round($chunkSizeBytes / 1024 / 1024, 2),
                'total_chunks' => $totalChunks
            ]);

            // Upload with progress tracking
            $uploadStatus = false;
            $handle = fopen($videoPath, 'rb');
            $chunkCount = 0;
            $lastLogTime = time();
            $startTime = time();

            if (!$handle) {
                throw new \Exception('Cannot open video file for reading');
            }

            while (!$uploadStatus && !feof($handle)) {
                $chunkCount++;
                $chunk = fread($handle, $chunkSizeBytes);

                if ($chunk === false) {
                    throw new \Exception('Failed to read chunk from video file');
                }

                // Log progress every 10 chunks or every 30 seconds
                $currentTime = time();
                if ($chunkCount % 10 === 0 || ($currentTime - $lastLogTime) >= 30) {
                    $progress = round(($chunkCount / $totalChunks) * 100, 1);
                    $memoryMB = round(memory_get_usage(true) / 1024 / 1024, 2);
                    $elapsedSeconds = $currentTime - $startTime;

                    Log::info("Upload progress: chunk {$chunkCount}/{$totalChunks} ({$progress}%)", [
                        'memory_mb' => $memoryMB,
                        'elapsed_seconds' => $elapsedSeconds
                    ]);

                    $lastLogTime = $currentTime;
                }

                try {
                    $uploadStatus = $media->nextChunk($chunk);
                } catch (\Exception $e) {
                    Log::error("Upload failed at chunk {$chunkCount}", [
                        'error' => $e->getMessage(),
                        'chunk_count' => $chunkCount,
                        'total_chunks' => $totalChunks
                    ]);
                    fclose($handle);
                    throw $e;
                }

                // Memory cleanup every 20 chunks
                if ($chunkCount % 20 === 0) {
                    gc_collect_cycles();
                }
            }

            fclose($handle);
            $client->setDefer(false);

            // Validate upload result
            if (!$uploadStatus || !isset($uploadStatus['id'])) {
                throw new \Exception('Upload completed but no video ID returned');
            }

            $youtubeVideoId = $uploadStatus['id'];

            Log::info('Upload completed successfully', [
                'youtube_video_id' => $youtubeVideoId,
                'total_chunks' => $chunkCount,
                'final_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'total_time_seconds' => time() - $startTime
            ]);

            // Update video record
            $this->video->markAsUploaded($youtubeVideoId);

            // Clean up file after successful upload
            try {
                Storage::disk('local')->delete($this->video->video_file);
                Log::info('Video file deleted after successful upload');
            } catch (\Exception $e) {
                Log::warning('Failed to delete video file', ['error' => $e->getMessage()]);
                // Don't throw exception for file cleanup failure
            }

            $videoType = $this->video->video_type === 'short' ? 'YouTube Shorts' : 'Video dài';
            Log::info("=== {$videoType} Upload Job Completed Successfully ===", [
                'video_id' => $this->video->id,
                'youtube_id' => $youtubeVideoId,
                'title' => $this->video->title,
                'upload_time' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            $this->handleJobFailure($e);
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    private function handleJobFailure(\Exception $e): void
    {
        Log::error('=== YouTube Upload Job Failed ===', [
            'video_id' => $this->video->id ?? 'unknown',
            'attempt' => $this->attempts(),
            'max_tries' => $this->tries,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);

        // Update video status
        try {
            $this->video->refresh();
            $errorMessage = $e->getMessage();

            // Add attempt info to error message
            if ($this->attempts() < $this->tries) {
                $errorMessage .= " (Attempt {$this->attempts()}/{$this->tries}, will retry)";
            }

            $this->video->markAsFailed($errorMessage);
        } catch (\Exception $updateError) {
            Log::error('Failed to update video status after job failure', [
                'video_id' => $this->video->id ?? 'unknown',
                'update_error' => $updateError->getMessage()
            ]);
        }
    }

    /**
     * Handle permanent job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('YouTube upload job failed permanently', [
            'video_id' => $this->video->id ?? 'unknown',
            'title' => $this->video->title ?? 'unknown',
            'video_type' => $this->video->video_type ?? 'long',
            'total_attempts' => $this->tries,
            'error' => $exception->getMessage()
        ]);

        try {
            $this->video->refresh();
            $this->video->markAsFailed(
                'Job failed permanently after ' . $this->tries . ' attempts: ' . $exception->getMessage()
            );
        } catch (\Exception $e) {
            Log::error('Failed to update video status after permanent failure', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get optimal chunk size based on file size
     */
    private function getOptimalChunkSize(int $fileSize): int
    {
        $fileSizeMB = $fileSize / (1024 * 1024);

        if ($fileSizeMB < 50) {
            return 1 * 1024 * 1024; // 1MB for small files
        } elseif ($fileSizeMB < 200) {
            return 2 * 1024 * 1024; // 2MB for medium files
        } elseif ($fileSizeMB < 500) {
            return 4 * 1024 * 1024; // 4MB for large files
        } else {
            return 8 * 1024 * 1024; // 8MB for very large files
        }
    }

    /**
     * Calculate backoff delay
     */
    public function backoff(): array
    {
        return $this->backoff;
    }
}