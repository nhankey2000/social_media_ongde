<?php
// File: app/Filament/Resources/ImageMenuResource/Pages/ListImageMenus.php

namespace App\Filament\Resources\ImageMenuResource\Pages;

use App\Filament\Resources\ImageMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImageMenus extends ListRecords
{
    protected static string $resource = ImageMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Thêm ảnh mới')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Danh sách ảnh menu';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Có thể thêm widgets ở đây
        ];
    }
}