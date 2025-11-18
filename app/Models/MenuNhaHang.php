<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MenuNhaHang extends Model
{
    use HasFactory;

    protected $table = 'menu_nha_hang';
    protected $fillable = ['img', 'sort_order'];
    protected $casts = ['sort_order' => 'integer'];

    // GIỮ NGUYÊN ACCESSOR CHO BLADE + FILAMENT
    protected function img(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null,
        );
    }

    // THÊM DÒNG NÀY – QUAN TRỌNG NHẤT!
    protected $appends = []; // ← BỎ accessor img khỏi API (tránh lỗi asset() khi query)
}