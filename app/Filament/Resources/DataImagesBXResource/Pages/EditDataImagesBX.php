<?php

namespace App\Filament\Resources\DataImagesBXResource\Pages;

use App\Filament\Resources\DataImagesBXResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDataImagesBX extends EditRecord
{
    protected static string $resource = DataImagesBXResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load file hiện tại để hiển thị trong FileUpload
        if (isset($data['url']) && $data['url']) {
            $filePath = str_replace(Storage::url(''), '', $data['url']);
            $data['files'] = [$filePath];
        } else {
            $data['files'] = [];
        }

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $files = $data['files'] ?? [];

        if (!empty($files)) {
            // Lấy file đầu tiên để update record hiện tại
            $firstFile = $files[0];
            $filePath = $firstFile->store('media-bx-files', 'public'); // Đổi thành media-bx-files
            $url = Storage::url($filePath);

            $record->update([
                'post_id' => $data['post_id'] ?? null,
                'type' => $data['type'],
                'id_danhmuc_data' => $data['id_danhmuc_data'],
                'url' => $url,
            ]);

            // Tạo records mới cho các file còn lại
            for ($i = 1; $i < count($files); $i++) {
                $filePath = $files[$i]->store('media-bx-files', 'public'); // Đổi thành media-bx-files
                $url = Storage::url($filePath);

                static::getModel()::create([
                    'post_id' => $data['post_id'] ?? null,
                    'type' => $data['type'],
                    'id_danhmuc_data' => $data['id_danhmuc_data'],
                    'url' => $url,
                ]);
            }
        } else {
            // Nếu không có file mới, chỉ update các field khác
            $record->update([
                'post_id' => $data['post_id'] ?? null,
                'type' => $data['type'],
                'id_danhmuc_data' => $data['id_danhmuc_data'],
            ]);
        }

        return $record;
    }
}