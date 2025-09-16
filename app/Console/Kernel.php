<?php

namespace App\Console;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Task monitor
        $schedule->call(function () {
            Log::info('Task schedule chạy OK lúc: ' . now());
        })->everyMinute();

        // Giảm tần suất các task
        $schedule->command('posts:publish-scheduled')->everyFiveMinutes();
        $schedule->command('prompts:process')->everyFiveMinutes();
        $schedule->command('analytics:sync')->dailyAt('02:00'); // Chuyển sang hàng ngày
        $schedule->command('instagram:process')->everyFiveMinutes();

        // Tạm vô hiệu hóa YouTube upload để test login
        // $schedule->command('youtube:upload-scheduled --limit=2') // Giảm limit
        //     ->everyFiveMinutes() // Giảm tần suất
        //     ->withoutOverlapping(10)
        //     ->runInBackground()
        //     ->appendOutputTo(storage_path('logs/youtube-scheduler.log'));

        // Reset stuck videos
        $schedule->command('youtube:reset-stuck --minutes=30')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/youtube-reset.log'));

        // Log YouTube status
        $schedule->call(function () {
            try {
                $pendingCount = \App\Models\YouTubeVideo::getPendingUploadCount();
                $uploadedTodayCount = \App\Models\YouTubeVideo::getUploadedTodayCount();
                $stuckCount = \App\Models\YouTubeVideo::getStuckVideos()->count();
                $totalVideos = \App\Models\YouTubeVideo::count();

                if ($pendingCount > 0 || $stuckCount > 0) {
                    Log::info('YouTube Scheduler Status', [
                        'pending_uploads' => $pendingCount,
                        'uploaded_today' => $uploadedTodayCount,
                        'stuck_videos' => $stuckCount,
                        'total_videos' => $totalVideos,
                        'timestamp' => now()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error getting YouTube stats', ['error' => $e->getMessage()]);
            }
        })->everyFiveMinutes();

        // Daily cleanup and reporting
        $schedule->call(function () {
            try {
                $stats = [
                    'total_videos' => \App\Models\YouTubeVideo::count(),
                    'uploaded_today' => \App\Models\YouTubeVideo::getUploadedTodayCount(),
                    'failed_videos' => \App\Models\YouTubeVideo::failed()->count(),
                    'pending_videos' => \App\Models\YouTubeVideo::getPendingUploadCount(),
                    'shorts_count' => \App\Models\YouTubeVideo::shorts()->count(),
                    'long_videos_count' => \App\Models\YouTubeVideo::longVideos()->count(),
                ];

                Log::info('Daily YouTube Stats', $stats);
            } catch (\Exception $e) {
                Log::error('Error generating daily YouTube stats', ['error' => $e->getMessage()]);
            }
        })->dailyAt('23:59');

        // Weekly cleanup
        $schedule->call(function () {
            try {
                $oldFailedCount = \App\Models\YouTubeVideo::where('upload_status', 'failed')
                    ->where('updated_at', '<', now()->subDays(7))
                    ->count();

                if ($oldFailedCount > 0) {
                    Log::info("Weekly cleanup: Found {$oldFailedCount} old failed uploads (>7 days)", [
                        'count' => $oldFailedCount
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error during weekly cleanup', ['error' => $e->getMessage()]);
            }
        })->weeklyOn(1, '02:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}