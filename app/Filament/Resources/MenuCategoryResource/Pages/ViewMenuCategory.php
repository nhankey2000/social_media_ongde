<?php
// File: app/Filament/Resources/MenuCategoryResource/Pages/ViewMenuCategory.php

namespace App\Filament\Resources\MenuCategoryResource\Pages;

use App\Filament\Resources\MenuCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMenuCategory extends ViewRecord
{
    protected static string $resource = MenuCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Chỉnh sửa'),
        ];
    }

    public function getTitle(): string
    {
        return 'Xem danh mục: ' . $this->record->name;
    }
}