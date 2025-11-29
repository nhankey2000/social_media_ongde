<?php

namespace App\Filament\Resources\TelegramMemberResource\Widgets;

use App\Models\TelegramMember;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TelegramMemberStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total = TelegramMember::count();
        $active = TelegramMember::where('is_active', true)->count();
        $withRole = TelegramMember::whereNotNull('role')->count();
        $withTasks = TelegramMember::has('taskAssignments')->count();

        return [
            Stat::make('Tổng Members', $total)
                ->description('Tất cả members trong hệ thống')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 12, 15, 20, 25, 30, $total]),

            Stat::make('Đang Active', $active)
                ->description(round($total > 0 ? ($active / $total) * 100 : 0, 1) . '% của tổng')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Có Vai Trò', $withRole)
                ->description(round($total > 0 ? ($withRole / $total) * 100 : 0, 1) . '% đã phân vai trò')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Đã Có Tasks', $withTasks)
                ->description('Members đã được giao việc')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning'),
        ];
    }
}