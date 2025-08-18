<?php
// File: app/Filament/Resources/ImageMenuResource/Pages/EditImageMenu.php

namespace App\Filament\Resources\ImageMenuResource\Pages;

use App\Filament\Resources\ImageMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditImageMenu extends EditRecord
{
    protected static string $resource = ImageMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Xóa ảnh')
                ->requiresConfirmation()
                ->modalHeading('Xóa ảnh')
                ->modalDescription('Bạn có chắc chắn muốn xóa ảnh này?')
                ->modalSubmitActionLabel('Xóa')
                ->successNotificationTitle('Ảnh đã được xóa!'),
        ];
    }

    public function getTitle(): string
    {
        return 'Chỉnh sửa ảnh: ' . $this->record->menuCategory->name;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Thành công')
            ->body('Ảnh menu đã được cập nhật!')
            ->duration(5000);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Có thể xử lý data trước khi lưu ở đây
        return $data;
    }
}