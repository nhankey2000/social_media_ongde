<?php
// File: app/Filament/Resources/MenuCategoryResource/Pages/EditMenuCategory.php

namespace App\Filament\Resources\MenuCategoryResource\Pages;

use App\Filament\Resources\MenuCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMenuCategory extends EditRecord
{
    protected static string $resource = MenuCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Xem'),
            Actions\DeleteAction::make()
                ->label('Xóa')
                ->requiresConfirmation(),
        ];
    }

    public function getTitle(): string
    {
        return 'Chỉnh sửa: ' . $this->record->name;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Thành công')
            ->body('Danh mục menu đã được cập nhật!');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}