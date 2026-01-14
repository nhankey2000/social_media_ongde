<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
class DriveWebhookController extends Controller
{
    public function download(Request $request)
    {
        $driveUrl = $request->file_url;

        // Lấy file ID từ Google Drive
        preg_match('/\/d\/(.*?)\//', $driveUrl, $matches);
        $fileId = $matches[1] ?? null;

        if (!$fileId) {
            return response()->json([
                'error' => 'Invalid Google Drive link'
            ], 400);
        }

        // Link tải trực tiếp (không bị robots.txt)
        $downloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}";

        $fileName = 'video_' . time() . '.mp4';
        $path = 'uploads/videos/' . $fileName;

        // Download file
        $response = Http::withOptions([
            'stream' => true,
        ])->get($downloadUrl);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Download failed'
            ], 500);
        }

        Storage::disk('public')->put($path, $response->body());

        $fullPath = storage_path('app/public/' . $path);
        $fileSize = filesize($fullPath);

        return response()->json([
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $fileSize,
            'public_url' => asset('storage/' . $path),
        ]);
    }
}
