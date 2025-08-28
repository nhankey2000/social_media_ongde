<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class YouTubeVideo extends Model
{
    protected $table = 'youtube_videos';

    protected $fillable = [
        'platform_account_id',
        'video_id',
        'title',
        'video_file',
        'description',
        'category_id',
        'status',
        'video_type',
        'scheduled_at',
        'upload_status',
        'upload_error',
        'uploaded_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'uploaded_at' => 'datetime',
    ];

    protected $attributes = [
        'upload_status' => 'pending',
        'video_type' => 'long',
        'status' => 'public',
        'category_id' => '22',
    ];

    // Relationships
    public function platformAccount(): BelongsTo
    {
        return $this->belongsTo(PlatformAccount::class);
    }

    // Scopes
    public function scopePendingUpload($query)
    {
        return $query->whereNull('video_id')
            ->where(function($q) {
                $q->where('upload_status', 'pending')
                    ->orWhere(function($sq) {
                        // Include stuck uploading videos older than 30 minutes
                        $sq->where('upload_status', 'uploading')
                            ->where('updated_at', '<', now()->subMinutes(30));
                    });
            })
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
            ->whereNull('video_id');
    }

    public function scopeShorts($query)
    {
        return $query->where('video_type', 'short');
    }

    public function scopeLongVideos($query)
    {
        return $query->where('video_type', 'long');
    }

    public function scopeUploaded($query)
    {
        return $query->whereNotNull('video_id');
    }

    public function scopeFailed($query)
    {
        return $query->where('upload_status', 'failed');
    }

    public function scopeReadyToUpload($query)
    {
        return $query->pendingUpload();
    }

    // Status Methods
    public function isUploaded(): bool
    {
        return !is_null($this->video_id);
    }

    public function isPending(): bool
    {
        return $this->upload_status === 'pending' && is_null($this->video_id);
    }

    public function isUploading(): bool
    {
        return $this->upload_status === 'uploading';
    }

    public function isFailed(): bool
    {
        return $this->upload_status === 'failed';
    }

    public function isReadyToUpload(): bool
    {
        return $this->isPending()
            && !is_null($this->scheduled_at)
            && $this->scheduled_at <= now();
    }

    public function isStuck(): bool
    {
        return $this->upload_status === 'uploading'
            && $this->updated_at < now()->subMinutes(30);
    }

    public function isScheduled(): bool
    {
        return !is_null($this->scheduled_at) && $this->scheduled_at > now();
    }

    // Video Type Methods
    public function isShort(): bool
    {
        return $this->video_type === 'short';
    }

    public function isLong(): bool
    {
        return $this->video_type === 'long' || is_null($this->video_type);
    }

    // File Methods
    public function hasValidFile(): bool
    {
        return $this->video_file && Storage::disk('local')->exists($this->video_file);
    }

    public function getFileSize(): int
    {
        if (!$this->hasValidFile()) {
            return 0;
        }
        return Storage::disk('local')->size($this->video_file);
    }

    public function getFileSizeMB(): float
    {
        return round($this->getFileSize() / (1024 * 1024), 2);
    }

    public function getFilePath(): ?string
    {
        if (!$this->hasValidFile()) {
            return null;
        }
        return Storage::disk('local')->path($this->video_file);
    }

    // Content Methods
    public function getFormattedDescription(): string
    {
        $description = $this->description;

        if ($this->isShort() && !str_contains(strtolower($description), '#shorts')) {
            $description = "#Shorts\n\n" . $description;
        }

        return $description;
    }

    public function getOptimizedTitle(): string
    {
        if ($this->isShort()) {
            $title = $this->title;
            if (!str_contains(strtolower($title), '#shorts') && !str_contains(strtolower($title), 'shorts')) {
                $title = $title . ' #Shorts';
            }
            return $title;
        }

        return $this->title;
    }

    public function getOptimizedDescription(): string
    {
        if ($this->isShort()) {
            $description = "#Shorts #YouTubeShorts\n\n" . $this->description;

            $viralTags = ['#Viral', '#Trending', '#MustWatch'];
            $selectedTags = array_rand(array_flip($viralTags), min(3, count($viralTags)));
            if (is_string($selectedTags)) $selectedTags = [$selectedTags];

            $description .= "\n\n" . implode(' ', $selectedTags);
            return $description;
        }

        return $this->description;
    }

    public function getAutoTags(): array
    {
        $tags = [];

        if ($this->isShort()) {
            $tags = ['Shorts', 'YouTubeShorts', 'Short', 'Viral', 'Trending'];

            // Add content-based tags for Shorts
            $content = strtolower($this->title . ' ' . $this->description);

            if (str_contains($content, 'funny') || str_contains($content, 'hài') || str_contains($content, 'comedy')) {
                $tags[] = 'Comedy';
                $tags[] = 'Funny';
            }

            if (str_contains($content, 'music') || str_contains($content, 'nhạc') || str_contains($content, 'song')) {
                $tags[] = 'Music';
                $tags[] = 'Song';
            }

            if (str_contains($content, 'dance') || str_contains($content, 'nhảy')) {
                $tags[] = 'Dance';
                $tags[] = 'Dancing';
            }
        }

        // Extract hashtags from description
        if ($this->description) {
            preg_match_all('/#(\w+)/', $this->description, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $tag) {
                    if (!in_array(strtolower($tag), array_map('strtolower', $tags)) && count($tags) < 15) {
                        $tags[] = ucfirst(strtolower($tag));
                    }
                }
            }
        }

        return array_unique($tags);
    }

    public function getOptimizedCategoryId(): string
    {
        if ($this->isShort()) {
            return '24'; // Entertainment - best for Shorts
        }

        return $this->category_id;
    }

    // Update Methods
    public function markAsUploading(): void
    {
        $this->update(['upload_status' => 'uploading']);
    }

    public function markAsUploaded(string $youtubeId): void
    {
        $this->update([
            'video_id' => $youtubeId,
            'upload_status' => 'uploaded',
            'uploaded_at' => now(),
            'upload_error' => null
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'upload_status' => 'failed',
            'upload_error' => $error
        ]);
    }

    public function resetToPending(): void
    {
        $this->update([
            'upload_status' => 'pending',
            'upload_error' => null
        ]);
    }

    // Attributes
    protected function uploadStatusText(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->isUploaded()) {
                    return 'Đã đăng';
                }

                if (is_null($this->scheduled_at)) {
                    return 'Chưa lên lịch';
                }

                if ($this->scheduled_at > now()) {
                    return 'Đã lên lịch';
                }

                if ($this->upload_status === 'uploading') {
                    return 'Đang đăng';
                }

                if ($this->upload_status === 'failed') {
                    return 'Lỗi đăng';
                }

                return 'Chờ đăng';
            }
        );
    }

    protected function uploadStatusColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->isUploaded()) {
                    return 'success';
                }

                if (is_null($this->scheduled_at)) {
                    return 'gray';
                }

                if ($this->scheduled_at > now()) {
                    return 'info';
                }

                if ($this->upload_status === 'uploading') {
                    return 'warning';
                }

                if ($this->upload_status === 'failed') {
                    return 'danger';
                }

                return 'primary';
            }
        );
    }

    protected function videoTypeText(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->video_type) {
                    'short' => 'YouTube Shorts',
                    'long' => 'Video Dài',
                    default => 'Video Dài',
                };
            }
        );
    }

    protected function videoTypeIcon(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->video_type) {
                    'short' => 'heroicon-o-bolt',
                    'long' => 'heroicon-o-video-camera',
                    default => 'heroicon-o-video-camera',
                };
            }
        );
    }

    protected function videoTypeColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->video_type) {
                    'short' => 'warning',
                    'long' => 'info',
                    default => 'info',
                };
            }
        );
    }

    // Helper Methods
    public function getRecommendedDuration(): string
    {
        return match($this->video_type) {
            'short' => 'Tối đa 60 giây',
            'long' => 'Không giới hạn',
            default => 'Không giới hạn',
        };
    }

    public function getRecommendedFormat(): string
    {
        return match($this->video_type) {
            'short' => 'Video dọc (9:16), MP4 khuyến nghị',
            'long' => 'Video ngang (16:9), MP4/WebM',
            default => 'MP4, MPEG hoặc WebM',
        };
    }

    public function getYouTubeUrl(): ?string
    {
        return $this->video_id ? "https://www.youtube.com/watch?v={$this->video_id}" : null;
    }

    // Static Methods
    public static function getPendingUploadCount(): int
    {
        return static::pendingUpload()->count();
    }

    public static function getUploadedTodayCount(): int
    {
        return static::whereDate('uploaded_at', today())->count();
    }

    public static function getTodaysScheduledCount(): int
    {
        return static::whereDate('scheduled_at', today())->count();
    }

    public static function getStuckVideos()
    {
        return static::where('upload_status', 'uploading')
            ->where('updated_at', '<', now()->subMinutes(30))
            ->get();
    }

    public static function resetStuckVideos(): int
    {
        return static::where('upload_status', 'uploading')
            ->where('updated_at', '<', now()->subMinutes(30))
            ->update([
                'upload_status' => 'pending',
                'upload_error' => 'Reset - stuck upload process'
            ]);
    }
}