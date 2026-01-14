<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriveWebhookController extends Controller
{
    public function download(Request $request)
    {
        $url = $request->input('file_url');

        if (!$url) {
            return response()->json(['error' => 'file_url is required'], 400);
        }

        // ✅ Lấy FILE_ID
        if (!preg_match('/(?:\/d\/|id=)([a-zA-Z0-9_-]{10,})/', $url, $m)) {
            return response()->json(['error' => 'Invalid Drive URL'], 400);
        }

        $fileId = $m[1];
        $downloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}";

        $client = new Client([
            'allow_redirects' => true,
            'timeout' => 0,
            'verify' => false,
        ]);

        // ✅ Request dạng stream
        $response = $client->request('GET', $downloadUrl, [
            'stream' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0',
            ],
        ]);

        $stream = $response->getBody();

        // ✅ Lấy filesize nếu có
        $fileSize = $response->getHeaderLine('Content-Length');

        return new StreamedResponse(function () use ($stream) {
            while (!$stream->eof()) {
                echo $stream->read(1024 * 1024); // 1MB chunk
                flush();
            }
        }, 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="drive_file"',
            'Content-Length'      => $fileSize ?: null,
            'Cache-Control'       => 'no-cache',
        ]);
    }
}
