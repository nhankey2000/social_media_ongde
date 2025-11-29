<?php

namespace App\Filament\Resources\TelegramMemberResource\Pages;

use App\Filament\Resources\TelegramMemberResource;
use App\Models\TelegramMember;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTelegramMember extends EditRecord
{
    protected static string $resource = TelegramMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update full_name nếu thay đổi first/last name
        if (isset($data['first_name']) || isset($data['last_name'])) {
            $data['full_name'] = trim(
                ($data['first_name'] ?? $this->record->first_name) . ' ' .
                ($data['last_name'] ?? $this->record->last_name ?? '')
            );
        }

        // Auto-generate keywords nếu role thay đổi và chưa có keywords
        if (isset($data['role']) && $data['role'] !== $this->record->role) {
            if (empty($data['keywords'])) {
                $data['keywords'] = TelegramMember::generateKeywords($data['role']);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}