<?php

namespace App\Filament\Resources\DataPostNHResource\Pages;

use App\Filament\Resources\DataPostNHResource;
use App\Models\DataPostNH;
use App\Models\DataImagesNH;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class CreateDataPostNH extends CreateRecord
{
    protected static string $resource = DataPostNHResource::class;

    protected function handleRecordCreation(array $data): DataPostNH
    {
        $files = $data['files'] ?? [];

        // Remove files từ data để tạo DataPostNH
        unset($data['files']);
        unset($data['existing_files']);

        // Tạo DataPostNH record
        $post = DataPostNH::create($data);

        // Lưu files vào bảng DataImagesNH
        if (!empty($files)) {
            $createdFiles = 0;
            foreach ($files as $file) {
                DataImagesNH::create([
                    'post_id' => $post->id,
                    'type' => $post->type,
                    'id_danhmuc_data' => $post->id_danhmuc_data,
                    'url' => Storage::url($file),
                ]);
                $createdFiles++;
            }

            // Gửi notification thành công
            Notification::make()
                ->title('Thành công')
                ->body("Đã tạo post NH và lưu {$createdFiles} file vào thư viện.")
                ->success()
                ->send();
        }

        return $post;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}