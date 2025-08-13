<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPostBX extends Model
{
    use HasFactory;

    protected $table = 'data_postbx';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'content',
        'type',

        'id_danhmuc_data',
    ];

    // Mối quan hệ hasMany với DataImagesNH
    public function dataImagesBX()
    {
        return $this->hasMany(DataImagesBX::class, 'post_id', 'id');
    }

    // Mối quan hệ belongsTo với DanhmucNHS
    public function danhmucBX()
    {
        return $this->belongsTo(DanhmucBX::class, 'id_danhmuc_data', 'id');
    }
}