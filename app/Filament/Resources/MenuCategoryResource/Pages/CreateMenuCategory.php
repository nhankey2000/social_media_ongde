<?php
// File: app/Filament/Resources/MenuCategoryResource/Pages/CreateMenuCategory.php

namespace App\Filament\Resources\MenuCategoryResource\Pages;

use App\Filament\Resources\MenuCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateMenuCategory extends CreateRecord
{
    protected static string $resource = MenuCategoryResource::class;

    public function getTitle(): string
    {
        return 'Tạo danh mục menu';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Form data before create: ', $data); // Ghi log để debug
        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Thành công')
            ->body('Danh mục menu đã được tạo thành công!');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}