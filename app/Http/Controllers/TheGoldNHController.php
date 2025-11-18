<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TheGoldNHController extends Controller
{
    /**
     * Hiển thị trang khu vườn ma quái
     */
    public function index()
    {
        return view('thegoldNH'); // Bỏ 'licenses.'
    }
}
