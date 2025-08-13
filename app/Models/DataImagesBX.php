<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataImagesBX extends Model
{
    use HasFactory;

    protected $table = 'data_imagesbx';

    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'post_id',
        'type',

        'url',
        'id_danhmuc_data',
    ];

    // Mối quan hệ belongsTo với DataPostNH
    public function dataPostBX()
    {
        return $this->belongsTo(DataPostBX::class, 'post_id', 'id');
    }

    // Mối quan hệ belongsTo với DanhmucNHS
    public function danhmucBX()
    {
        return $this->belongsTo(DanhmucBX::class, 'id_danhmuc_data', 'id');
    }
}