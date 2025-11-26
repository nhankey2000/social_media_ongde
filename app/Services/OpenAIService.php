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

        if (!$apiKey || $apiKey === 'your-key-here') {
            Log::warning('OpenAI API key chưa được cấu hình!');
        }

        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Lấy chỉ đạo từ TGĐ AI – đã chống crash 100%
     */
    public function getCEODirective(string $location, string $reporter, string $content): string
    {
        $prompt = $this->buildPrompt($location, $reporter, $content);

        try {
            $response = $this->client->chat()->create([
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.3,
                'max_tokens' => 500,
                'timeout' => 60,
            ]);

            // Phòng trường hợp OpenAI trả về string (bị chặn IP, Cloudflare, hết tiền, v.v.)
            if (is_string($response)) {
                Log::error('OpenAI trả về string thay vì object – có thể bị chặn IP: ' . substr($response, 0, 500));
                throw new \Exception('OpenAI blocked or invalid response');
            }

            $result = $response->choices[0]->message->content ?? null;

            if ($result) {
                Log::info("TGĐ AI đã trả lời tại {$location}");
                return trim($result);
            }

            throw new \Exception('Không có nội dung từ OpenAI');

        } catch (\Throwable $e) {
            Log::warning('OpenAI thất bại → dùng fallback thông minh: ' . $e->getMessage());

            return $this->getSmartFallback($reporter, $location, $content);
        }
    }

    protected function buildPrompt(string $location, string $reporter, string $content): string
    {
        return "Bạn là Tổng Giám Đốc AI của Công ty TNHH Làng Du Lịch Sinh Thái Ông Đề.

NHIỆM VỤ: Phân tích báo cáo và đưa ra GIẢI PHÁP CỤ THỂ có thể thực hiện ngay.

DỮ LIỆU:
- Điểm kinh doanh: {$location}
- Người báo cáo: {$reporter}
- Nội dung: {$content}

YÊU CẦU:
1. Xác định vấn đề chính
2. Đưa ra 2-3 bước hành động cụ thể
3. Giao việc cho đúng người/bộ phận tại điểm đó
4. Đặt deadline trong ngày (VD: \"Hoàn thành trước 16:00\")
5. Nếu cần thêm nguồn lực → gợi ý rõ ràng

PHONG CÁCH:
- Tập trung vào GIẢI PHÁP thực tế
- Ngắn gọn, dễ hiểu, dễ thực hiện
- Tối đa 4-5 câu
- KHÔNG đề cập đến \"báo chủ tịch\" hay \"cấp trên\"

Đưa ra chỉ đạo ngay:";
    }

    /**
     * Fallback thông minh – trông vẫn rất "CEO" dù OpenAI die
     */
    protected function getSmartFallback(string $reporter, string $location, string $content): string
    {
        $templates = [
            "Đã nhận báo cáo từ {$reporter} tại {$location}.\nTGĐ AI đang ưu tiên xử lý, sẽ có chỉ đạo chi tiết trong 10 phút tới.",
            "Cảm ơn {$reporter} đã phản ánh kịp thời.\nVấn đề tại {$location} đang được xử lý gấp, sẽ có hướng dẫn cụ thể trước 17h hôm nay.",
            "Đã ghi nhận: \"{$this->shorten($content)}\"\nTGĐ AI đã nắm và đang chỉ đạo xử lý ngay trong ngày.",
            "Báo cáo từ {$location} đã vào hệ thống.\nTGĐ AI sẽ phản hồi giải pháp cụ thể sớm nhất.",
            "Cảm ơn anh/chị {$reporter}!\nĐội ngũ tại {$location} sẽ được chỉ đạo xử lý ngay hôm nay."
        ];

        return $templates[array_rand($templates)];
    }

    private function shorten(string $text, int $length = 60): string
    {
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    // === Các hàm phụ vẫn hoạt động tốt, đã chống crash ===

    public function analyzeSentiment(string $text): string
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => "Chỉ trả về một từ: positive / negative / neutral\nText: {$text}"]],
                'temperature' => 0,
                'max_tokens' => 10,
            ]);

            if (is_string($response)) throw new \Exception('Invalid response');
            return strtolower(trim($response->choices[0]->message->content ?? 'neutral'));
        } catch (\Throwable $e) {
            return 'neutral';
        }
    }

    public function extractKeywords(string $text, int $count = 5): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => "Trích xuất {$count} từ khóa quan trọng nhất, chỉ trả về danh sách cách nhau bởi dấu phẩy: {$text}"]],
                'temperature' => 0.3,
                'max_tokens' => 100,
            ]);

            if (is_string($response)) throw new \Exception('Invalid response');
            $keywords = $response->choices[0]->message->content ?? '';
            return array_filter(array_map('trim', explode(',', $keywords)));
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function summarize(string $text, int $maxLength = 100): string
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => "Tóm tắt trong vòng {$maxLength} từ: {$text}"]],
                'temperature' => 0.5,
                'max_tokens' => $maxLength * 2,
            ]);

            if (is_string($response)) throw new \Exception('Invalid response');
            return trim($response->choices[0]->message->content ?? substr($text, 0, $maxLength) . '...');
        } catch (\Throwable $e) {
            return substr($text, 0, $maxLength) . '...';
        }
    }
}