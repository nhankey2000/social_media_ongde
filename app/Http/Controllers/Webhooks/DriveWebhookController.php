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
        $driveUrl = $request->input('file_url');

        preg_match('/id=([^&]+)/', $driveUrl, $matches);
        $fileId = $matches[1] ?? null;

        if (!$fileId) {
            return response()->json(['error' => 'Invalid Drive URL'], 400);
        }

        // ===== 1️⃣ Request đầu: lấy confirm token =====
        $firstResponse = Http::withOptions([
            'cookies' => true,
        ])->get("https://drive.google.com/uc?export=download&id={$fileId}");

        preg_match('/confirm=([0-9A-Za-z_]+)/', $firstResponse->body(), $tokenMatch);
        $confirmToken = $tokenMatch[1] ?? null;

        // ===== 2️⃣ URL tải thật =====
        $downloadUrl = $confirmToken
            ? "https://drive.google.com/uc?export=download&confirm={$confirmToken}&id={$fileId}"
            : "https://drive.google.com/uc?export=download&id={$fileId}";

        // ===== 3️⃣ Stream binary =====
        $streamResponse = Http::withOptions([
            'stream' => true,
            'cookies' => true,
        ])->get($downloadUrl);

        if (!$streamResponse->successful()) {
            return response()->json(['error' => 'Download failed'], 500);
        }

        // ===== 4️⃣ File size =====
        $fileSize = (int) ($streamResponse->header('Content-Length') ?? 0);

        return new StreamedResponse(function () use ($streamResponse) {
            foreach ($streamResponse->body() as $chunk) {
                echo $chunk;
                flush();
            }
        }, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="video.mp4"',
            'X-File-Size' => $fileSize,
            'Cache-Control' => 'no-store',
        ]);
    }
}
