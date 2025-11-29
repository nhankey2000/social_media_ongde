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

        // Ensure keywords is array (Filament TagsInput returns array)
        if (isset($data['keywords']) && !is_array($data['keywords'])) {
            $data['keywords'] = is_string($data['keywords'])
                ? array_map('trim', explode(',', $data['keywords']))
                : [];
        }

        // Clean keywords - remove empty values
        if (isset($data['keywords']) && is_array($data['keywords'])) {
            $data['keywords'] = array_values(array_filter($data['keywords'], fn($k) => !empty(trim($k))));
        }

        // Log for debugging
        \Log::info('EditTelegramMember - Saving data', [
            'member_id' => $this->record->id,
            'keywords_before' => $this->record->keywords,
            'keywords_after' => $data['keywords'] ?? null,
            'role' => $data['role'] ?? null
        ]);

        return $data;
    }

    protected function afterSave(): void
    {
        // Force reload model from database
        $this->record->refresh();

        // Verify keywords were saved
        $keywordsCount = is_array($this->record->keywords) ? count($this->record->keywords) : 0;

        \Log::info('EditTelegramMember - After save verification', [
            'member_id' => $this->record->id,
            'keywords_in_db' => $this->record->keywords,
            'keywords_count' => $keywordsCount
        ]);

        // Clear any caching
        \Cache::forget("telegram_member_{$this->record->id}");

        // Show notification with keywords count
        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Đã lưu thành công!')
            ->body("Keywords: {$keywordsCount} từ khóa")
            ->duration(3000)
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}