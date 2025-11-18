<?php

namespace App\Filament\Resources\MenuNhaHangResource\Pages;

use App\Filament\Resources\MenuNhaHangResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateMenuNhaHang extends CreateRecord
{
    protected static string $resource = MenuNhaHangResource::class;

    // Đây là cách đúng nhất để ngăn Filament lưu mảng vào cột string
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $images = Arr::pull($data, 'img', []); // Lấy mảng ảnh ra, xóa khỏi $data

        $records = collect();

        foreach ($images as $index => $path) {
            $records->push(
                static::getModel()::create([
                    'img'        => $path,
                    'sort_order' => $index + 1,
                ])
            );
        }

        // Trả về bản ghi đầu tiên (hoặc null) để Filament không báo lỗi
        return $records->first() ?? new (static::getModel());
    }
}