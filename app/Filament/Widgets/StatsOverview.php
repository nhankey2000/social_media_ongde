<?php

namespace App\Filament\Widgets;

use App\Models\Report;
use App\Models\Location;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $today = now()->startOfDay();
        
        // Today's reports
        $todayCount = Report::whereDate('created_at', $today)->count();
        $yesterdayCount = Report::whereDate('created_at', $today->copy()->subDay())->count();
        $todayTrend = $yesterdayCount > 0 
            ? (($todayCount - $yesterdayCount) / $yesterdayCount) * 100 
            : 0;
        
        // In Progress
        $inProgressCount = Report::where('status', 'in_progress')->count();
        $pendingCount = Report::where('status', 'pending')->count();
        
        // Overdue
        $overdueCount = Report::where('status', 'overdue')->count();
        $overdueChange = $overdueCount - Report::where('status', 'overdue')
            ->whereDate('updated_at', $today->copy()->subDay())
            ->count();
        
        // Completed
        $completedCount = Report::where('status', 'completed')->count();
        $completedToday = Report::where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->count();
        
        return [
            Stat::make('Báo cáo hôm nay', $todayCount)
                ->description($todayTrend >= 0 
                    ? "+{$todayTrend}% so với hôm qua" 
                    : "{$todayTrend}% so với hôm qua"
                )
                ->descriptionIcon($todayTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 4, 6, 8, 6, 9, $todayCount]),
            
            Stat::make('Đang xử lý', $inProgressCount)
                ->description("{$pendingCount} đang chờ xử lý")
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([3, 5, 4, 6, $inProgressCount]),
            
            Stat::make('Quá hạn', $overdueCount)
                ->description($overdueChange > 0 
                    ? "+{$overdueChange} từ hôm qua" 
                    : "Không thay đổi"
                )
                ->descriptionIcon($overdueChange > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
                ->color('danger')
                ->chart([2, 3, 2, 4, $overdueCount]),
            
            Stat::make('Đã hoàn thành', $completedCount)
                ->description("{$completedToday} hoàn thành hôm nay")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([10, 15, 12, 18, 20, 25, $completedCount]),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}
