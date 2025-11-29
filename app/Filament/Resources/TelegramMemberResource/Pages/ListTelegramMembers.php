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