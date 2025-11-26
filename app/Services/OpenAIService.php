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
            throw new \Exception('OpenAI API key chưa được cấu hình! Vui lòng kiểm tra file .env');
        }

        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Get CEO directive from AI
     */
    public function getCEODirective($location, $username, $text)
    {
        try {
            Log::info('Calling OpenAI API...', [
                'location' => $location,
                'username' => $username
            ]);

            $systemPrompt = $this->buildPrompt($location, $username, $text);

            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo', // ← ĐỔI MODEL (rẻ và nhanh)
                // Hoặc dùng: 'gpt-3.5-turbo' nếu muốn rẻ hơn
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt // ← DÙNG PROMPT ĐÃ BUILD
                    ],
                    [
                        'role' => 'user',
                        'content' => "Hãy đưa ra chỉ đạo cụ thể và có thể thực hiện ngay."
                    ]
                ],
                'max_tokens' => 300, // ← GIẢM để tiết kiệm
                'temperature' => 0.7,
            ]);

            $directive = $response->choices[0]->message->content;

            Log::info('OpenAI response received successfully');
            return $directive;

        } catch (\OpenAI\Exceptions\ErrorException $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());

            // Kiểm tra lỗi cụ thể
            $errorMsg = $e->getMessage();

            if (str_contains($errorMsg, 'invalid_api_key')) {
                throw new \Exception('API Key không hợp lệ. Kiểm tra lại OPENAI_API_KEY trong .env');
            } elseif (str_contains($errorMsg, 'insufficient_quota')) {
                throw new \Exception('Tài khoản OpenAI hết quota. Vui lòng nạp thêm credits tại platform.openai.com');
            } elseif (str_contains($errorMsg, 'model_not_found')) {
                throw new \Exception('Model không tồn tại. Vui lòng dùng gpt-4o-mini hoặc gpt-3.5-turbo');
            } elseif (str_contains($errorMsg, 'rate_limit')) {
                throw new \Exception('Đã vượt quá giới hạn request. Thử lại sau vài giây.');
            }

            throw new \Exception('Lỗi OpenAI API: ' . $errorMsg);

        } catch (\Exception $e) {
            Log::error('Unexpected OpenAI error: ' . $e->getMessage());
            throw new \Exception('Không thể kết nối OpenAI: ' . $e->getMessage());
        }
    }

    /**
     * Build prompt for CEO AI
     */
    protected function buildPrompt(string $location, string $reporter, string $content): string
    {
        return "Bạn là Tổng Giám Đốc AI của Công ty TNHH Làng Du Lịch Sinh Thái.

NHIỆM VỤ:
Phân tích báo cáo và đưa ra GIẢI PHÁP CỤ THỂ có thể thực hiện ngay.

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
     * Fallback response when API fails
     */
    protected function getFallbackResponse(): string
    {
        return "Đã nhận được báo cáo. Hệ thống AI tạm thời quá tải, " .
            "TGĐ AI sẽ phản hồi chi tiết trong vòng 15 phút. " .
            "Nếu khẩn cấp, vui lòng liên hệ hotline.";
    }

    /**
     * Analyze text sentiment
     */
    public function analyzeSentiment(string $text): string
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Phân tích cảm xúc của đoạn text sau (positive/negative/neutral): {$text}"
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 50,
            ]);

            return trim($response->choices[0]->message->content);

        } catch (\Exception $e) {
            Log::error('OpenAI sentiment analysis error: ' . $e->getMessage());
            return 'neutral';
        }
    }

    /**
     * Extract keywords from text
     */
    public function extractKeywords(string $text, int $count = 5): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Trích xuất {$count} từ khóa quan trọng nhất từ text sau (chỉ trả về danh sách từ khóa, cách nhau bởi dấu phẩy): {$text}"
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 100,
            ]);

            $keywords = $response->choices[0]->message->content;
            return array_map('trim', explode(',', $keywords));

        } catch (\Exception $e) {
            Log::error('OpenAI keyword extraction error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Summarize long text
     */
    public function summarize(string $text, int $maxLength = 100): string
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Tóm tắt đoạn text sau trong {$maxLength} từ: {$text}"
                    ]
                ],
                'temperature' => 0.5,
                'max_tokens' => $maxLength * 2,
            ]);

            return trim($response->choices[0]->message->content);

        } catch (\Exception $e) {
            Log::error('OpenAI summarization error: ' . $e->getMessage());
            return substr($text, 0, $maxLength) . '...';
        }
    }
}