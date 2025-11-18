<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TheDiamondNHController extends Controller
{
    /**
     * Hiển thị trang khu vườn ma quái
     */
    public function index()
    {
        return view('thediamondNH'); // Bỏ 'licenses.'
    }
}
