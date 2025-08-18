<?php

namespace App\Filament\Resources\ImageMenuResource\Pages;

use App\Filament\Resources\ImageMenuResource;
use App\Models\ImageMenu;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateImageMenu extends CreateRecord
{
    protected static string $resource = ImageMenuResource::class;

    public function getTitle(): string
    {
        return 'Thêm ảnh menu';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Thành công')
            ->body('Ảnh menu đã được thêm thành công!')
            ->duration(5000);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $images = $data['image_path'];
        $menuCategoryId = $data['menu_category_id'];

        // Kiểm tra và chuẩn hóa dữ liệu image_path
        if (!is_array($images)) {
            $images = [$images]; // Chuyển thành mảng nếu chỉ có một ảnh
        }

        // Loại bỏ các phần tử không phải chuỗi (nếu có)
        $images = array_filter($images, 'is_string');

        if (empty($images)) {
            Notification::make()
                ->danger()
                ->title('Lỗi')
                ->body('Không có ảnh nào được upload.')
                ->duration(5000)
                ->send();
            throw new \Exception('Không có ảnh nào được upload.');
        }

        // Tạo bản ghi đầu tiên để trả về (yêu cầu của Filament)
        $firstRecord = ImageMenu::create([
            'menu_category_id' => $menuCategoryId,
            'image_path' => $images[0], // Lưu ảnh đầu tiên
        ]);

        // Lưu các ảnh còn lại (nếu có)
        for ($i = 1; $i < count($images); $i++) {
            ImageMenu::create([
                'menu_category_id' => $menuCategoryId,
                'image_path' => $images[$i],
            ]);
        }

        // Ghi đè thông báo để hiển thị số lượng ảnh
        Notification::make()
            ->success()
            ->title('Thành công')
            ->body('Đã lưu ' . count($images) . ' ảnh vào danh mục.')
            ->duration(5000)
            ->send();

        return $firstRecord;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Giữ nguyên để tùy chỉnh thêm nếu cần
        return $data;
    }
}