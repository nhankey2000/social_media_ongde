<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhmucBX extends Model
{
    use HasFactory;

    protected $table = 'danhmuc_bx';

    protected $primaryKey = 'id';

    protected $fillable = [
        'ten_danh_muc',
    ];

    // Mối quan hệ hasMany với DataPostNH
    public function dataPostsBX()
    {
        return $this->hasMany(DataPostBX::class, 'id_danhmuc_data', 'id');
    }

    // Mối quan hệ hasMany với DataImagesNH
    public function dataImagesBX()
    {
        return $this->hasMany(DataImagesBX::class, 'id_danhmuc_data', 'id');
    }
}