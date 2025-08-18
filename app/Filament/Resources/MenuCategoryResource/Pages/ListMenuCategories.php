<?php
// File: app/Filament/Resources/MenuCategoryResource/Pages/ListMenuCategories.php

namespace App\Filament\Resources\MenuCategoryResource\Pages;

use App\Filament\Resources\MenuCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenuCategories extends ListRecords
{
    protected static string $resource = MenuCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tạo danh mục mới'),
        ];
    }

    public function getTitle(): string
    {
        return 'Danh sách danh mục menu';
    }
}