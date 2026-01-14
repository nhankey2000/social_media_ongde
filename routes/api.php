<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\DriveWebhookController;

Route::post('/webhook/download-drive', [DriveWebhookController::class, 'download']);