<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPostNH extends Model
{
    use HasFactory;

    protected $table = 'data_postnh';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'content',
        'type',

        'id_danhmuc_data',
    ];

    // Mối quan hệ hasMany với DataImagesNH
    public function dataImagesNH()
    {
        return $this->hasMany(DataImagesNH::class, 'post_id', 'id');
    }

    // Mối quan hệ belongsTo với DanhmucNHS
    public function danhmucNHS()
    {
        return $this->belongsTo(DanhmucNHS::class, 'id_danhmuc_data', 'id');
    }
}