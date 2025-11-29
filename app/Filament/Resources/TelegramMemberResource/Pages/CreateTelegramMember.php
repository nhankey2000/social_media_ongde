<?php

namespace App\Filament\Resources\TelegramMemberResource\Pages;

use App\Filament\Resources\TelegramMemberResource;
use App\Models\TelegramMember;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTelegramMember extends CreateRecord
{
    protected static string $resource = TelegramMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Tạo full_name từ first_name + last_name
        $data['full_name'] = trim($data['first_name'] . ' ' . ($data['last_name'] ?? ''));

        // Auto-detect role nếu chưa có
        if (empty($data['role'])) {
            $data['role'] = TelegramMember::detectRole($data['full_name']);
        }

        // Auto-generate keywords nếu chưa có
        if (empty($data['keywords']) && !empty($data['role'])) {
            $data['keywords'] = TelegramMember::generateKeywords($data['role']);
        }

        // Ensure keywords is array
        if (isset($data['keywords']) && !is_array($data['keywords'])) {
            $data['keywords'] = is_string($data['keywords'])
                ? array_map('trim', explode(',', $data['keywords']))
                : [];
        }

        // Log for debugging
        \Log::info('CreateTelegramMember - Creating', [
            'keywords' => $data['keywords'] ?? null,
            'role' => $data['role'] ?? null
        ]);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}