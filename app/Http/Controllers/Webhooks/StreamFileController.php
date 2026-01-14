<?php

namespace App\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamFileController extends Controller
{
    public function stream(Request $request)
    {
        $relativePath = $request->input('file_path');
        if (!$relativePath) {
            return response()->json(['error' => 'Missing file_path'], 400);
        }

        // Ví dụ: uploads/videos/video_xxx.mp4
        $fullPath = storage_path('app/public/' . $relativePath);

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $fileSize = filesize($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

        return new StreamedResponse(function () use ($fullPath) {
            $handle = fopen($fullPath, 'rb');
            while (!feof($handle)) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $fileSize,
            'Content-Disposition' => 'inline; filename="'.basename($fullPath).'"',

            // 🔥 CỰC QUAN TRỌNG – TẮT HẾT MỌI BIẾN ĐỔI
            'Cache-Control'       => 'no-store',
            'Content-Encoding'    => 'identity',
        ]);
    }
}
