<?php
//
//namespace App\Services;
//
//use OpenAI;
//use Illuminate\Support\Facades\Log;
//
//class OpenAIService
//{
//    protected $client;
//
//    public function __construct()
//    {
//        $apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');
//
//        if (!$apiKey || $apiKey === 'your-key-here') {
//            throw new \Exception('OpenAI API key chưa được cấu hình! Vui lòng kiểm tra file .env');
//        }
//
//        $this->client = OpenAI::client($apiKey);
//    }
//
//    /**
//     * Get CEO directive from AI
//     */
//    public function getCEODirective($location, $username, $text)
//    {
//        try {
//            Log::info('Calling OpenAI API...', [
//                'location' => $location,
//                'username' => $username
//            ]);
//
//            $systemPrompt = $this->buildPrompt($location, $username, $text);
//
//            $response = $this->client->chat()->create([
//                'model' => 'gpt-3.5-turbo', // ← ĐỔI MODEL (rẻ và nhanh)
//                // Hoặc dùng: 'gpt-3.5-turbo' nếu muốn rẻ hơn
//                'messages' => [
//                    [
//                        'role' => 'system',
//                        'content' => $systemPrompt // ← DÙNG PROMPT ĐÃ BUILD
//                    ],
//                    [
//                        'role' => 'user',
//                        'content' => "Hãy đưa ra chỉ đạo cụ thể và có thể thực hiện ngay."
//                    ]
//                ],
//                'max_tokens' => 300, // ← GIẢM để tiết kiệm
//                'temperature' => 0.7,
//            ]);
//
//            $directive = $response->choices[0]->message->content;
//
//            Log::info('OpenAI response received successfully');
//            return $directive;
//
//        } catch (\OpenAI\Exceptions\ErrorException $e) {
//            Log::error('OpenAI API Error: ' . $e->getMessage());
//
//            // Kiểm tra lỗi cụ thể
//            $errorMsg = $e->getMessage();
//
//            if (str_contains($errorMsg, 'invalid_api_key')) {
//                throw new \Exception('API Key không hợp lệ. Kiểm tra lại OPENAI_API_KEY trong .env');
//            } elseif (str_contains($errorMsg, 'insufficient_quota')) {
//                throw new \Exception('Tài khoản OpenAI hết quota. Vui lòng nạp thêm credits tại platform.openai.com');
//            } elseif (str_contains($errorMsg, 'model_not_found')) {
//                throw new \Exception('Model không tồn tại. Vui lòng dùng gpt-4o-mini hoặc gpt-3.5-turbo');
//            } elseif (str_contains($errorMsg, 'rate_limit')) {
//                throw new \Exception('Đã vượt quá giới hạn request. Thử lại sau vài giây.');
//            }
//
//            throw new \Exception('Lỗi OpenAI API: ' . $errorMsg);
//
//        } catch (\Exception $e) {
//            Log::error('Unexpected OpenAI error: ' . $e->getMessage());
//            throw new \Exception('Không thể kết nối OpenAI: ' . $e->getMessage());
//        }
//    }
//
//    /**
//     * Build prompt for CEO AI
//     */
//    protected function buildPrompt(string $location, string $reporter, string $content): string
//    {
//        return "Bạn là Tổng Giám Đốc AI của Công ty TNHH Làng Du Lịch Sinh Thái.
//
//NHIỆM VỤ:
//Phân tích báo cáo và đưa ra GIẢI PHÁP CỤ THỂ có thể thực hiện ngay.
//
//DỮ LIỆU:
//- Điểm kinh doanh: {$location}
//- Người báo cáo: {$reporter}
//- Nội dung: {$content}
//
//YÊU CẦU:
//1. Xác định vấn đề chính
//2. Đưa ra 2-3 bước hành động cụ thể
//3. Giao việc cho đúng người/bộ phận tại điểm đó
//4. Đặt deadline trong ngày (VD: \"Hoàn thành trước 16:00\")
//5. Nếu cần thêm nguồn lực → gợi ý rõ ràng
//
//PHONG CÁCH:
//- Tập trung vào GIẢI PHÁP thực tế
//- Ngắn gọn, dễ hiểu, dễ thực hiện
//- Tối đa 4-5 câu
//- KHÔNG đề cập đến \"báo chủ tịch\" hay \"cấp trên\"
//
//Đưa ra chỉ đạo ngay:";
//    }
//
//    /**
//     * Fallback response when API fails
//     */
//    protected function getFallbackResponse(): string
//    {
//        return "Đã nhận được báo cáo. Hệ thống AI tạm thời quá tải, " .
//            "TGĐ AI sẽ phản hồi chi tiết trong vòng 15 phút. " .
//            "Nếu khẩn cấp, vui lòng liên hệ hotline.";
//    }
//
//    /**
//     * Analyze text sentiment
//     */
//    public function analyzeSentiment(string $text): string
//    {
//        try {
//            $response = $this->client->chat()->create([
//                'model' => 'gpt-3.5-turbo',
//                'messages' => [
//                    [
//                        'role' => 'user',
//                        'content' => "Phân tích cảm xúc của đoạn text sau (positive/negative/neutral): {$text}"
//                    ]
//                ],
//                'temperature' => 0.1,
//                'max_tokens' => 50,
//            ]);
//
//            return trim($response->choices[0]->message->content);
//
//        } catch (\Exception $e) {
//            Log::error('OpenAI sentiment analysis error: ' . $e->getMessage());
//            return 'neutral';
//        }
//    }
//
//    /**
//     * Extract keywords from text
//     */
//    public function extractKeywords(string $text, int $count = 5): array
//    {
//        try {
//            $response = $this->client->chat()->create([
//                'model' => 'gpt-3.5-turbo',
//                'messages' => [
//                    [
//                        'role' => 'user',
//                        'content' => "Trích xuất {$count} từ khóa quan trọng nhất từ text sau (chỉ trả về danh sách từ khóa, cách nhau bởi dấu phẩy): {$text}"
//                    ]
//                ],
//                'temperature' => 0.3,
//                'max_tokens' => 100,
//            ]);
//
//            $keywords = $response->choices[0]->message->content;
//            return array_map('trim', explode(',', $keywords));
//
//        } catch (\Exception $e) {
//            Log::error('OpenAI keyword extraction error: ' . $e->getMessage());
//            return [];
//        }
//    }
//
//    /**
//     * Summarize long text
//     */
//    public function summarize(string $text, int $maxLength = 100): string
//    {
//        try {
//            $response = $this->client->chat()->create([
//                'model' => 'gpt-3.5-turbo',
//                'messages' => [
//                    [
//                        'role' => 'user',
//                        'content' => "Tóm tắt đoạn text sau trong {$maxLength} từ: {$text}"
//                    ]
//                ],
//                'temperature' => 0.5,
//                'max_tokens' => $maxLength * 2,
//            ]);
//
//            return trim($response->choices[0]->message->content);
//
//        } catch (\Exception $e) {
//            Log::error('OpenAI summarization error: ' . $e->getMessage());
//            return substr($text, 0, $maxLength) . '...';
//        }
//    }
//}


namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;
    protected $baseUrl;
    protected $model;

    public function __construct()
    {
        // Sử dụng Groq (MIỄN PHÍ)
        $this->apiKey = env('GROQ_API_KEY');
        $this->baseUrl = 'https://api.groq.com/openai/v1';
        $this->model = 'llama-3.3-70b-versatile';

        if (!$this->apiKey) {
            throw new \Exception('GROQ_API_KEY chưa được cấu hình trong .env');
        }
    }

    /**
     * Get CEO directive from AI
     */
    public function getCEODirective($location, $username, $text)
    {
        try {
            Log::info('Calling Groq API...', [
                'location' => $location,
                'username' => $username
            ]);

            $systemPrompt = $this->buildPrompt($location, $username, $text);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => 'Hãy đưa ra chỉ đạo cụ thể và có thể thực hiện ngay.'
                        ]
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]);

            if (!$response->successful()) {
                $error = $response->json();
                Log::error('Groq API Error', ['error' => $error]);
                throw new \Exception('Groq API Error: ' . ($error['error']['message'] ?? 'Unknown error'));
            }

            $data = $response->json();
            $directive = $data['choices'][0]['message']['content'];

            Log::info('Groq response received successfully');
            return $directive;

        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());

            // Trả về fallback response
            return $this->getFallbackResponse($location, $username, $text);
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
    protected function getFallbackResponse($location, $username, $text): string
    {
        return "✅ Đã nhận báo cáo từ {$username} tại {$location}.\n\n" .
            "📋 Nội dung: {$text}\n\n" .
            "🔧 CHỈ ĐẠO KHẨN:\n" .
            "1. Xử lý ngay vấn đề này\n" .
            "2. Báo cáo kết quả cho quản lý\n" .
            "3. Gọi IT support nếu cần hỗ trợ\n\n" .
            "⏰ Hoàn thành trước 17:00 hôm nay\n\n" .
            "⚠️ (Hệ thống AI đang bảo trì - đây là chỉ đạo tự động)";
    }

    /**
     * Analyze text sentiment
     */
    public function analyzeSentiment(string $text): string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Phân tích cảm xúc của đoạn text sau (chỉ trả lời: positive/negative/neutral): {$text}"
                        ]
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 10,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return trim(strtolower($data['choices'][0]['message']['content']));
            }

            return 'neutral';

        } catch (\Exception $e) {
            Log::error('Sentiment analysis error: ' . $e->getMessage());
            return 'neutral';
        }
    }

    /**
     * Extract keywords from text
     */
    public function extractKeywords(string $text, int $count = 5): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Trích xuất {$count} từ khóa quan trọng nhất từ text sau (chỉ trả về danh sách từ khóa, cách nhau bởi dấu phẩy): {$text}"
                        ]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 100,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $keywords = $data['choices'][0]['message']['content'];
                return array_map('trim', explode(',', $keywords));
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Keyword extraction error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Summarize long text
     */
    public function summarize(string $text, int $maxLength = 100): string
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Tóm tắt đoạn text sau trong {$maxLength} từ: {$text}"
                        ]
                    ],
                    'temperature' => 0.5,
                    'max_tokens' => $maxLength * 2,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return trim($data['choices'][0]['message']['content']);
            }

            return substr($text, 0, $maxLength) . '...';

        } catch (\Exception $e) {
            Log::error('Summarization error: ' . $e->getMessage());
            return substr($text, 0, $maxLength) . '...';
        }
    }
}