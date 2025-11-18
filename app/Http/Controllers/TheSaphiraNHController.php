<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TheSaphiraNHController extends Controller
{
    /**
     * Hiển thị trang khu vườn ma quái
     */
    public function index()
    {
        return view('TheSaphiraNH'); // Bỏ 'licenses.'
    }
}
