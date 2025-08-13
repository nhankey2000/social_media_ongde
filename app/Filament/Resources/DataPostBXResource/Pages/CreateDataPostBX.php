<?php

namespace App\Filament\Resources\DataPostBXResource\Pages;

use App\Filament\Resources\DataPostBXResource;
use App\Models\DataPostBX;
use App\Models\DataImagesBX;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class CreateDataPostBX extends CreateRecord
{
    protected static string $resource = DataPostBXResource::class;

    protected function handleRecordCreation(array $data): DataPostBX
    {
        $files = $data['files'] ?? [];

        // Remove files từ data để tạo DataPostBX
        unset($data['files']);
        unset($data['existing_files']);

        // Tạo DataPostBX record
        $post = DataPostBX::create($data);

        // Lưu files vào bảng DataImagesBX
        if (!empty($files)) {
            $createdFiles = 0;
            foreach ($files as $file) {
                // Kiểm tra và lưu file
                $filePath = is_string($file) ? $file : $file->store('data-post-bx-files', 'public');
                if (is_string($file) && Storage::disk('public')->exists($file)) {
                    $newFilePath = 'data-post-bx-files/' . basename($file);
                    Storage::disk('public')->move($file, $newFilePath);
                    $filePath = $newFilePath;
                }
                $url = Storage::url($filePath);

                DataImagesBX::create([
                    'post_id' => $post->id,
                    'type' => $post->type,
                    'id_danhmuc_data' => $post->id_danhmuc_data,
                    'url' => $url,
                ]);
                $createdFiles++;
            }

            // Gửi notification thành công
            Notification::make()
                ->title('Thành công')
                ->body("Đã tạo post BX và lưu {$createdFiles} file vào thư viện.")
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