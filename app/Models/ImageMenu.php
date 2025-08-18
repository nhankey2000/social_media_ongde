<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageMenu extends Model
{
    use HasFactory;

    protected $table = 'images_menu';

    protected $fillable = [
        'menu_category_id',
        'image_path'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    // Accessor để lấy URL đầy đủ của ảnh
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}