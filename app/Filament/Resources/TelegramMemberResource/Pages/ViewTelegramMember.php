<?php

namespace App\Filament\Resources\TelegramMemberResource\Pages;

use App\Filament\Resources\TelegramMemberResource;
use App\Services\TaskAssignmentService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewTelegramMember extends ViewRecord
{
    protected static string $resource = TelegramMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Thông tin cơ bản')
                    ->schema([
                        Components\TextEntry::make('location.name')
                            ->label('Location'),
                        Components\TextEntry::make('telegram_id')
                            ->label('Telegram ID')
                            ->copyable(),
                        Components\TextEntry::make('username')
                            ->label('Username')
                            ->formatStateUsing(fn ($state) => $state ? "@{$state}" : '-')
                            ->url(fn ($state) => $state ? "https://t.me/{$state}" : null)
                            ->openUrlInNewTab(),
                        Components\TextEntry::make('full_name')
                            ->label('Tên đầy đủ')
                            ->weight('bold'),
                        Components\TextEntry::make('role')
                            ->label('Vai trò')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'IT' => 'primary',
                                'Bảo trì' => 'success',
                                'Kế toán' => 'warning',
                                default => 'gray',
                            }),
                        Components\IconEntry::make('is_active')
                            ->label('Trạng thái')
                            ->boolean(),
                        Components\TextEntry::make('last_seen_at')
                            ->label('Last Seen')
                            ->dateTime('d/m/Y H:i')
                            ->since(),
                    ])
                    ->columns(3),

                Components\Section::make('Keywords')
                    ->schema([
                        Components\TextEntry::make('keywords')
                            ->label('Từ khóa để auto-assign')
                            ->badge()
                            ->placeholder('Chưa có keywords'),
                    ]),

                Components\Section::make('Thống kê Tasks')
                    ->schema([
                        Components\Grid::make(4)
                            ->schema([
                                Components\TextEntry::make('stats.total')
                                    ->label('Tổng Tasks')
                                    ->state(function ($record) {
                                        $service = app(TaskAssignmentService::class);
                                        $stats = $service->getMemberTaskStats($record);
                                        return $stats['total'];
                                    })
                                    ->badge()
                                    ->color('primary'),

                                Components\TextEntry::make('stats.active')
                                    ->label('Active')
                                    ->state(function ($record) {
                                        $service = app(TaskAssignmentService::class);
                                        $stats = $service->getMemberTaskStats($record);
                                        return $stats['assigned'] + $stats['acknowledged'];
                                    })
                                    ->badge()
                                    ->color('warning'),

                                Components\TextEntry::make('stats.completed')
                                    ->label('Hoàn thành')
                                    ->state(function ($record) {
                                        $service = app(TaskAssignmentService::class);
                                        $stats = $service->getMemberTaskStats($record);
                                        return $stats['completed'];
                                    })
                                    ->badge()
                                    ->color('success'),

                                Components\TextEntry::make('stats.avg_time')
                                    ->label('Avg Time')
                                    ->state(function ($record) {
                                        $service = app(TaskAssignmentService::class);
                                        $stats = $service->getMemberTaskStats($record);
                                        return round($stats['avg_completion_time'] ?? 0) . ' phút';
                                    })
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ]),

                Components\Section::make('Lịch sử Tasks')
                    ->schema([
                        Components\RepeatableEntry::make('taskAssignments')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('report_id')
                                    ->label('Report')
                                    ->formatStateUsing(fn ($state) => "#{$state}"),
                                Components\TextEntry::make('task_description')
                                    ->label('Nhiệm vụ'),
                                Components\TextEntry::make('status')
                                    ->label('Trạng thái')
                                    ->badge()
                                    ->color(fn ($state) => match($state) {
                                        'assigned' => 'primary',
                                        'acknowledged' => 'info',
                                        'completed' => 'success',
                                        default => 'gray',
                                    }),
                                Components\TextEntry::make('assigned_at')
                                    ->label('Giao lúc')
                                    ->dateTime('d/m H:i')
                                    ->since(),
                                Components\TextEntry::make('completed_at')
                                    ->label('Hoàn thành')
                                    ->dateTime('d/m H:i')
                                    ->placeholder('-'),
                            ])
                            ->columns(5)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}