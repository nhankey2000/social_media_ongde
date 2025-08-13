<?php

namespace App\Filament\Resources\DataImagesNHResource\Pages;

use App\Filament\Resources\DataImagesNHResource;
use App\Models\DataImagesNH;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class CreateDataImagesNH extends CreateRecord
{
    protected static string $resource = DataImagesNHResource::class;

    protected function handleRecordCreation(array $data): DataImagesNH
    {
        $files = $data['files'] ?? [];
        $type = $data['type'];
        $postId = $data['post_id'] ?? null;
        $idDanhmucData = $data['id_danhmuc_data'];

        if (empty($files)) {
            throw new \Exception('Vui lòng chọn ít nhất một file để upload.');
        }

        $createdRecords = [];

        foreach ($files as $file) {
            $recordData = [
                'post_id' => $postId,
                'type' => $type,
                'id_danhmuc_data' => $idDanhmucData,
                'url' => Storage::url($file),
            ];

            $record = DataImagesNH::create($recordData);
            $createdRecords[] = $record;
        }

        // Gửi notification thành công
        Notification::make()
            ->title('Thành công')
            ->body('Đã tạo thành công ' . count($createdRecords) . ' bản ghi.')
            ->success()
            ->send();

        // Return record đầu tiên (yêu cầu của Filament)
        return $createdRecords[0] ?? new DataImagesNH();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}