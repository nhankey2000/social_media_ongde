<?php

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

            // Phát hiện vấn đề tài chính
            $isFinancial = $this->detectFinancialIssue($text);

            $systemPrompt = $this->buildPrompt($location, $username, $text, $isFinancial);

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

            // Nếu là vấn đề tài chính, thêm thông báo
            if ($isFinancial) {
                $directive .= "\n\n🔴 *QUAN TRỌNG:* Vấn đề này liên quan đến tài chính/ngân sách.\n" .
                    "📤 Đã tự động chuyển báo cáo lên *Chủ Tịch Lê Hải Phúc* để phê duyệt.";
            }

            Log::info('Groq response received successfully', ['is_financial' => $isFinancial]);

            // Trả về array với thông tin đầy đủ
            return [
                'directive' => $directive,
                'is_financial' => $isFinancial,
                'needs_chairman_approval' => $isFinancial
            ];

        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());

            // Trả về fallback response
            $fallback = $this->getFallbackResponse($location, $username, $text);
            return [
                'directive' => $fallback,
                'is_financial' => false,
                'needs_chairman_approval' => false
            ];
        }
    }

    /**
     * Phát hiện vấn đề liên quan tài chính
     */
    protected function detectFinancialIssue(string $text): bool
    {
        $financialKeywords = [
            // Tiền bạc
            'tiền', 'đồng', 'vnd', 'triệu', 'nghìn', 'tỷ', 'ngàn',
            'chi phí', 'kinh phí', 'ngân sách', 'budget',

            // Mua sắm
            'mua', 'sắm', 'đặt hàng', 'order', 'thanh toán', 'payment',
            'hóa đơn', 'invoice', 'chi', 'trả tiền',

            // Đầu tư
            'đầu tư', 'invest', 'trang thiết bị', 'thiết bị mới',
            'nâng cấp', 'upgrade', 'bổ sung',

            // Nhân sự
            'tuyển', 'recruitment', 'lương', 'salary', 'thưởng', 'bonus',
            'tăng lương', 'phụ cấp', 'trợ cấp',

            // Khác
            'phê duyệt', 'approval', 'xin phép', 'cần tiền',
            'hết tiền', 'thiếu tiền', 'cần mua', 'giá'
        ];

        $textLower = mb_strtolower($text);

        foreach ($financialKeywords as $keyword) {
            if (str_contains($textLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build prompt for CEO AI
     */
    protected function buildPrompt(string $location, string $reporter, string $content, bool $isFinancial = false): string
    {
        $basePrompt = "Bạn là Tổng Giám Đốc AI của Công ty TNHH Làng Du Lịch Sinh Thái.

NHIỆM VỤ:
Phân tích báo cáo và đưa ra GIẢI PHÁP CỤ THỂ có thể thực hiện ngay.

DỮ LIỆU:
- Điểm kinh doanh: {$location}
- Người báo cáo: {$reporter}
- Nội dung: {$content}";

        if ($isFinancial) {
            $basePrompt .= "\n\n⚠️ *LƯU Ý ĐẶC BIỆT:*
Đây là vấn đề liên quan đến TÀI CHÍNH/NGÂN SÁCH.
- KHÔNG tự ý phê duyệt chi tiêu
- CHỈ đưa ra đánh giá sơ bộ và mức độ cần thiết
- Nhấn mạnh rằng quyết định cuối cùng thuộc về Chủ Tịch Lê Hải Phúc
- Gợi ý các thông tin cần bổ sung để Chủ Tịch xem xét";
        }

        $basePrompt .= "\n\nYÊU CẦU:
1. Xác định vấn đề chính
2. Đưa ra 2-3 bước hành động cụ thể";

        if (!$isFinancial) {
            $basePrompt .= "\n3. Giao việc cho đúng người/bộ phận tại điểm đó
4. Đặt deadline trong ngày (VD: \"Hoàn thành trước 16:00\")
5. Nếu cần thêm nguồn lực → gợi ý rõ ràng";
        } else {
            $basePrompt .= "\n3. Đánh giá mức độ cấp thiết (khẩn cấp/quan trọng/bình thường)
4. Ước tính chi phí sơ bộ (nếu có thông tin)
5. Gợi ý thông tin cần bổ sung cho Chủ Tịch";
        }

        $basePrompt .= "\n\nPHONG CÁCH:
- Tập trung vào GIẢI PHÁP thực tế
- Ngắn gọn, dễ hiểu, dễ thực hiện
- Tối đa 4-5 câu
- KHÔNG đề cập đến \"báo chủ tịch\" hay \"cấp trên\" (TRỪNG KHI là vấn đề tài chính)

Đưa ra chỉ đạo ngay:";

        return $basePrompt;
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