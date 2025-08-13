<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataImagesNH extends Model
{
    use HasFactory;

    protected $table = 'data_imagesnh';

    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'post_id',
        'type',

        'url',
        'id_danhmuc_data',
    ];

    // Mối quan hệ belongsTo với DataPostNH
    public function dataPostNH()
    {
        return $this->belongsTo(DataPostNH::class, 'post_id', 'id');
    }

    // Mối quan hệ belongsTo với DanhmucNHS
    public function danhmucNHS()
    {
        return $this->belongsTo(DanhmucNHS::class, 'id_danhmuc_data', 'id');
    }
}