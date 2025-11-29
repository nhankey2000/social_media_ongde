<?php

namespace App\Filament\Resources\TelegramMemberResource\Pages;

use App\Filament\Resources\TelegramMemberResource;
use App\Models\TelegramMember;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTelegramMembers extends ListRecords
{
    protected static string $resource = TelegramMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_telegram')
                ->label('Sync từ Telegram')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sync Members từ Telegram')
                ->modalDescription('Chọn location để quét và lưu tất cả members từ Telegram group')
                ->form([
                    \Filament\Forms\Components\Select::make('location_id')
                        ->label('Chọn Location')
                        ->options(\App\Models\Location::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->placeholder('Chọn location cần sync'),
                ])
                ->action(function (array $data) {
                    $location = \App\Models\Location::find($data['location_id']);
                    $service = app(\App\Services\TelegramMemberService::class);

                    $result = $service->syncGroupMembers($location);

                    if ($result['success']) {
                        $stats = $result['stats'];
                        \Filament\Notifications\Notification::make()
                            ->title('✅ Sync thành công!')
                            ->body("Mới: {$stats['new']}, Cập nhật: {$stats['updated']}, Tổng: {$stats['total']}")
                            ->success()
                            ->duration(5000)
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('❌ Lỗi khi sync!')
                            ->body($result['error'])
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make(),
        ];
    }

    // COMMENT OUT WIDGETS NẾU BỊ LỖI
    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         \App\Filament\Resources\TelegramMemberResource\Widgets\TelegramMemberStatsWidget::class,
    //     ];
    // }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả'),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(TelegramMember::where('is_active', true)->count()),

            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(TelegramMember::where('is_active', false)->count()),

            'it' => Tab::make('IT')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'IT'))
                ->badge(TelegramMember::where('role', 'IT')->count()),

            'maintenance' => Tab::make('Bảo trì')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'Bảo trì'))
                ->badge(TelegramMember::where('role', 'Bảo trì')->count()),

            'accounting' => Tab::make('Kế toán')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'Kế toán'))
                ->badge(TelegramMember::where('role', 'Kế toán')->count()),

            'no_role' => Tab::make('Chưa có role')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('role'))
                ->badge(TelegramMember::whereNull('role')->count()),
        ];
    }
}