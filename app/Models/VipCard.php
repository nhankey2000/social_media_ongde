<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipCard extends Model
{
    protected $table = 'vip_cards';

    protected $fillable = [
        'type',
        'content',
        'expiry_date',
        'created_date',
        'updated_date',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'created_date' => 'date',
        'updated_date' => 'date',
        'status' => 'boolean',
    ];
}