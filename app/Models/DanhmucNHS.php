<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhmucNHS extends Model
{
    use HasFactory;

    protected $table = 'danhmuc_n_h_s';

    protected $primaryKey = 'id';

    protected $fillable = [
        'ten_danh_muc',
    ];

    // Mối quan hệ hasMany với DataPostNH
    public function dataPostsNH()
    {
        return $this->hasMany(DataPostNH::class, 'id_danhmuc_data', 'id');
    }

    // Mối quan hệ hasMany với DataImagesNH
    public function dataImagesNH()
    {
        return $this->hasMany(DataImagesNH::class, 'id_danhmuc_data', 'id');
    }
}