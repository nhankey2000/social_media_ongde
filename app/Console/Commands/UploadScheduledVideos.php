<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\YouTubeVideo;
use App\Jobs\UploadYouTubeVideoJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadScheduledVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:upload-scheduled
                            {--limit=5 : Số lượng video tối đa upload mỗi lần chạy}
                            {--dry-run : Chỉ hiển thị video sẽ được upload, không thực hiện upload}
                            {--debug : Hiển thị thông tin debug chi tiết}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload scheduled YouTube videos automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $debug = $this->option('debug');

        $this->info('🔍 Tìm kiếm video cần upload...');

        if ($debug) {
            $this->info("Debug: Tìm video với điều kiện:");
            $this->info("- upload_status = 'pending' hoặc NULL");
            $this->info("- scheduled_at <= " . now());
            $this->info("- video_id IS NULL");
            $this->info("- Limit: {$limit}");
        }

        // Kiểm tra method pendingUpload có tồn tại không
        if (!method_exists(YouTubeVideo::class, 'scopePendingUpload')) {
            $this->error("❌ Method 'pendingUpload' không tồn tại trong YouTubeVideo model!");
            $this->info("Sử dụng query thủ công...");

            // Fallback query nếu scope không tồn tại
            $videos = YouTubeVideo::where(function($q) {
                $q->where('upload_status', 'pending')
                    ->orWhereNull('upload_status');
            })
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->whereNull('video_id')
                ->with('platformAccount')
                ->orderBy('scheduled_at')
                ->limit($limit)
                ->get();
        } else {
            // Sử dụng scope nếu có
            $videos = YouTubeVideo::pendingUpload()
                ->with('platformAccount')
                ->orderBy('scheduled_at')
                ->limit($limit)
                ->get();
        }

        if ($debug) {
            $totalVideos = YouTubeVideo::count();
            $pendingVideos = YouTubeVideo::where('upload_status', 'pending')->count();
            $scheduledVideos = YouTubeVideo::whereNotNull('scheduled_at')->count();

            $this->info("Debug thống kê:");
            $this->info("- Total videos: {$totalVideos}");
            $this->info("- Pending videos: {$pendingVideos}");
            $this->info("- Scheduled videos: {$scheduledVideos}");
            $this->info("- Current time: " . now());
        }

        if ($videos->isEmpty()) {
            $this->info('✅ Không có video nào cần upload vào thời điểm này.');

            if ($debug) {
                // Debug: Hiển thị video gần nhất
                $nextVideo = YouTubeVideo::whereNotNull('scheduled_at')
                    ->where('scheduled_at', '>', now())
                    ->orderBy('scheduled_at')
                    ->first();

                if ($nextVideo) {
                    $this->info("Debug: Video tiếp theo sẽ được upload lúc: " . $nextVideo->scheduled_at);
                }

                $uploadingVideos = YouTubeVideo::where('upload_status', 'uploading')->get();
                if ($uploadingVideos->count() > 0) {
                    $this->warn("Debug: Có {$uploadingVideos->count()} video đang ở trạng thái 'uploading':");
                    foreach ($uploadingVideos as $video) {
                        $this->warn("- ID {$video->id}: {$video->title} (updated: {$video->updated_at})");
                    }
                }
            }

            return 0;
        }

        $this->info("📹 Tìm thấy {$videos->count()} video cần upload:");
        $this->newLine();

        // Hiển thị danh sách video
        $headers = ['ID', 'Tiêu đề', 'Kênh', 'Lịch đăng', 'Trạng thái', 'File tồn tại'];
        $rows = [];

        foreach ($videos as $video) {
            $fileExists = $video->video_file && Storage::disk('local')->exists($video->video_file);

            $rows[] = [
                $video->id,
                $this->truncate($video->title, 30),
                $video->platformAccount->name ?? 'N/A',
                $video->scheduled_at->format('d/m/Y H:i'),
                $video->upload_status ?? 'pending',
                $fileExists ? '✅' : '❌'
            ];
        }

        $this->table($headers, $rows);

        if ($dryRun) {
            $this->warn('🧪 Chế độ dry-run: Không thực hiện upload thực tế.');

            // Trong dry-run, vẫn kiểm tra các vấn đề tiềm ẩn
            $problemVideos = $videos->filter(function($video) {
                return !$video->video_file || !Storage::disk('local')->exists($video->video_file);
            });

            if ($problemVideos->count() > 0) {
                $this->error("⚠️  Phát hiện {$problemVideos->count()} video có vấn đề với file:");
                foreach ($problemVideos as $video) {
                    $this->error("- ID {$video->id}: File không tồn tại");
                }
            }

            return 0;
        }

        // Xác nhận trước khi upload
        if (!$this->confirm("Bạn có chắc chắn muốn upload {$videos->count()} video này?")) {
            $this->info('❌ Đã hủy.');
            return 0;
        }

        $this->info('🚀 Bắt đầu upload...');
        $this->newLine();

        $successCount = 0;
        $failCount = 0;

        foreach ($videos as $video) {
            $this->info("📤 Đang xử lý: {$video->title}");

            try {
                // Kiểm tra chi tiết file
                if (!$video->video_file) {
                    throw new \Exception('Không có file video được chỉ định');
                }

                if (!Storage::disk('local')->exists($video->video_file)) {
                    throw new \Exception("File video không tồn tại: {$video->video_file}");
                }

                // Kiểm tra kích thước file
                $fileSize = Storage::disk('local')->size($video->video_file);
                $fileSizeMB = round($fileSize / (1024 * 1024), 2);

                if ($fileSize === 0) {
                    throw new \Exception('File video có kích thước 0 bytes');
                }

                if ($debug) {
                    $this->info("   📁 File size: {$fileSizeMB} MB");
                }

                // Kiểm tra platform account
                if (!$video->platformAccount) {
                    throw new \Exception('Không tìm thấy kênh YouTube được liên kết');
                }

                // Dispatch job
                UploadYouTubeVideoJob::dispatch($video);

                // Cập nhật trạng thái
                $video->update(['upload_status' => 'uploading']);

                $successCount++;
                $this->info("   ✅ Đã thêm vào queue");

                Log::info('Video added to upload queue', [
                    'video_id' => $video->id,
                    'title' => $video->title,
                    'scheduled_at' => $video->scheduled_at,
                    'file_size_mb' => $fileSizeMB,
                    'video_type' => $video->video_type ?? 'long'
                ]);

            } catch (\Exception $e) {
                $failCount++;
                $this->error("   ❌ Lỗi: {$e->getMessage()}");

                // Cập nhật trạng thái lỗi với thông tin chi tiết
                $video->update([
                    'upload_status' => 'failed',
                    'upload_error' => $e->getMessage()
                ]);

                Log::error('Failed to queue video for upload', [
                    'video_id' => $video->id,
                    'title' => $video->title,
                    'error' => $e->getMessage(),
                    'video_file' => $video->video_file,
                    'platform_account_id' => $video->platform_account_id
                ]);
            }
        }

        $this->newLine();
        $this->info("🎯 Kết quả:");
        $this->info("   ✅ Thành công: {$successCount}");
        if ($failCount > 0) {
            $this->error("   ❌ Thất bại: {$failCount}");
        }

        if ($successCount > 0) {
            $this->info("📋 Video đã được thêm vào queue.");

            // Kiểm tra queue worker có chạy không
            $this->checkQueueWorker();
        }

        return $failCount > 0 ? 1 : 0;
    }

    /**
     * Check if queue worker is running
     */
    private function checkQueueWorker(): void
    {
        $processes = shell_exec('ps aux | grep "queue:work" | grep -v grep');

        if (empty($processes)) {
            $this->warn("⚠️  Cảnh báo: Không phát hiện queue worker đang chạy!");
            $this->info("Chạy lệnh sau để xử lý queue:");
            $this->info("php artisan queue:work --queue=default --timeout=1800");
        } else {
            $this->info("✅ Queue worker đang hoạt động");
        }
    }

    /**
     * Truncate text to specified length
     */
    private function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3) . '...';
    }
}