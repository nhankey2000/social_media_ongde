<?php

namespace App\Services;

use OpenAI;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');
        $this->client = $apiKey ? OpenAI::client($apiKey) : null;
    }

    public function getCEODirective(string $location, string $reporter, string $content): string
    {
        // Nếu không có key → trả lời chào trước đã
        if (!$this->client) {
            return $this->smartReply($reporter, $content);
        }

        $prompt = $this->buildPrompt($location, $reporter, $content);

        try {
            $response = $this->client->chat()->create([
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.3,
                'max_tokens' => 500,
                'timeout' => 50,
            ]);

            // Đây là chỗ hay bị lỗi nhất khi bị block IP
            if (is_string($response)) {
                Log::warning("OpenAI bị block IP, trả string: " . substr($response, 0, 300));
                throw new \Exception('OpenAI blocked');
            }

            return trim($response->choices[0]->message->content ?? "TGĐ AI đang bận, sẽ phản hồi sớm!");

        } catch (\Throwable $e) {
            Log::warning("OpenAI lỗi ({$e->getMessage()}) → dùng fallback thông minh");
            return $this->smartReply($reporter, $content);
        }
    }

    private function smartReply(string $reporter, string $content): string
    {
        $content = trim(strtolower($content));

        // Trả lời đặc biệt cho lời chào
        if (in_array($content, ['xin chào', 'hello', 'hi', 'chào', 'alo', 'sếp ơi', 'ai đó'])) {
            $greetings = [
                "Chào {$reporter}! TGĐ AI đây ạ! Có gì cần chỉ đạo không anh? 😄",
                "Alo alo, {$reporter} gọi TGĐ AI có việc gì gấp hả? 🔥",
                "Chào buổi sáng {$reporter}! Hôm nay điểm Ông Đề thế nào rồi? ☀️",
                "TGĐ AI có mặt! {$reporter} báo cáo đi nào! 💪",
            ];
            return $greetings[array_rand($greetings)];
        }

        // Các phản hồi thông minh khác
        $replies = [
            "Đã nhận tin từ {$reporter} tại {$location}.\nTGĐ AI đang xử lý gấp, sẽ có chỉ đạo trong vài phút nữa!",
            "Cảm ơn {$reporter} đã báo cáo!\nĐang phân tích và sẽ có hướng dẫn cụ thể ngay hôm nay.",
            "Đã ghi nhận: \"{$content}\"\nTGĐ AI sẽ phản hồi giải pháp chi tiết sớm nhất có thể.",
            "Báo cáo đã vào hệ thống. TGĐ AI đang chỉ đạo xử lý ngay trong ngày!",
        ];

        return $replies[array_rand($replies)];
    }

    protected function buildPrompt(string $location, string $reporter, string $content): string
    {
        return "Bạn là Tổng Giám Đốc AI của Công ty TNHH Làng Du Lịch Sinh Thái Ông Đề.

DỮ LIỆU:
- Điểm kinh doanh: {$location}
- Người báo cáo: {$reporter}
- Nội dung: {$content}

Hãy đưa ra chỉ đạo cụ thể, ngắn gọn, có deadline trong ngày, giao đúng người, không nhắc cấp trên.

Trả lời ngay:";
    }
}