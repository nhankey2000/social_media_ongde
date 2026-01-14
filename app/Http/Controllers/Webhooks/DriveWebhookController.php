<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriveWebhookController extends Controller
{
    public function download(Request $request)
    {
        $fileUrl = $request->input('file_url');

        if (!$fileUrl) {
            return response()->json([
                'error' => 'file_url is required'
            ], 422);
        }

        // 1️⃣ HEAD để lấy chính xác file size (BYTE)
        $head = Http::withoutVerifying()->head($fileUrl);

        if (!$head->ok()) {
            return response()->json([
                'error' => 'Cannot fetch file info'
            ], 400);
        }

        $fileSize = (int) ($head->header('Content-Length') ?? 0);
        $mimeType = $head->header('Content-Type') ?? 'application/octet-stream';

        if ($fileSize <= 0) {
            return response()->json([
                'error' => 'Invalid file size'
            ], 400);
        }

        // 2️⃣ Stream file về dạng binary
        $stream = Http::withoutVerifying()
            ->withOptions(['stream' => true])
            ->get($fileUrl);

        if (!$stream->ok()) {
            return response()->json([
                'error' => 'Failed to download file'
            ], 500);
        }

        // 3️⃣ Trả về StreamedResponse (binary)
        return new StreamedResponse(function () use ($stream) {
            foreach ($stream->toPsrResponse()->getBody() as $chunk) {
                echo $chunk;
                flush();
            }
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $fileSize,      // ✅ BYTE
            'X-Entity-Length'     => $fileSize,      // ✅ BYTE
            'Content-Disposition'=> 'inline',
        ]);
    }
}
