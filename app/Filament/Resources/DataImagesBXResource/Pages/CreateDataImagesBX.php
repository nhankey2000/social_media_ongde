<?php

namespace App\Filament\Resources\DataImagesBXResource\Pages;

use App\Filament\Resources\DataImagesBXResource;
use App\Models\DataImagesBX;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class CreateDataImagesBX extends CreateRecord
{
    protected static string $resource = DataImagesBXResource::class;

    protected function handleRecordCreation(array $data): DataImagesBX
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
            try {
                // Kiểm tra xem $file là string (đường dẫn tạm) hay UploadedFile
                $filePath = is_string($file) ? $file : $file->store('media-bx-files', 'public');
                if (!$filePath) {
                    throw new \Exception('Không thể lưu tệp tin.');
                }

                // Nếu $file là đường dẫn tạm, di chuyển nó đến vị trí mới
                if (is_string($file) && Storage::disk('public')->exists($file)) {
                    $newFilePath = 'media-bx-files/' . basename($file);
                    Storage::disk('public')->move($file, $newFilePath);
                    $filePath = $newFilePath;
                }

                // Tạo đường dẫn đầy đủ cho url
                $url = Storage::url($filePath);

                $recordData = [
                    'post_id' => $postId,
                    'type' => $type,
                    'id_danhmuc_data' => $idDanhmucData,
                    'url' => $url,
                ];

                $record = DataImagesBX::create($recordData);
                $createdRecords[] = $record;
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Lỗi')
                    ->body('Không thể tạo bản ghi cho tệp ' . (basename($file) ?? 'không xác định') . ': ' . $e->getMessage())
                    ->danger()
                    ->send();
                continue; // Tiếp tục với tệp tiếp theo nếu có lỗi
            }
        }

        // Gửi notification thành công nếu có ít nhất một bản ghi được tạo
        if (!empty($createdRecords)) {
            Notification::make()
                ->title('Thành công')
                ->body('Đã tạo thành công ' . count($createdRecords) . ' bản ghi.')
                ->success()
                ->send();
        } else {
            throw new \Exception('Không tạo được bất kỳ bản ghi nào.');
        }

        // Return record đầu tiên (yêu cầu của Filament)
        return $createdRecords[0] ?? new DataImagesBX();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}