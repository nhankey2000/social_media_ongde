<?php

namespace App\Services;

use TelegramBot\Api\BotApi;
use App\Models\Location;
use App\Models\Report;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramBotService
{
    protected BotApi $bot;
    protected OpenAIService $openAI;

    public function __construct()
    {
        // Hardcode token để chạy ngay
        $this->bot = new BotApi('7617448862:AAH7G_WdSzFugy0xqouoxEl1s9xOLy4gwy0');

        $this->openAI = new OpenAIService();
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function handleWebhook(array $update): void
    {
        try {
            // Log raw update để debug
            Log::info('Telegram webhook received', ['update' => $update]);

            if (!isset($update['message'])) {
                Log::info('Telegram webhook: No message in update');
                return;
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $chatTitle = $message['chat']['title'] ?? 'Private Chat';
            $chatType = $message['chat']['type'] ?? 'private';
            $text = $message['text'] ?? '';
            $from = $message['from'];

            $username = $from['first_name'] ?? 'Unknown';
            $telegramId = $from['id'] ?? null;
            $telegramUsername = $from['username'] ?? null;

            Log::info("Telegram message from {$username} ({$chatId}): {$text}");

            // Find or create location by chat_id
            $location = Location::where('chat_id', $chatId)->first();

            if (!$location) {
                // AUTO-CREATE LOCATION
                $location = $this->autoCreateLocation($chatId, $chatTitle, $chatType);

                // Send welcome message
                $this->sendWelcomeMessage($chatId, $location);
            }

            // Check for commands
            if (str_starts_with($text, '/')) {
                $this->handleCommand($chatId, $text, $location);
                return;
            }

            // Check if completion report
            if ($this->isCompletionReport($text)) {
                $this->handleCompletion($chatId, $location, $username, $telegramId, $text);
                return;
            }

            // Handle regular report
            $this->handleReport($chatId, $location, $username, $telegramId, $telegramUsername, $text);

        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Gửi thông báo lỗi về Telegram nếu có chatId
            if (isset($chatId)) {
                try {
                    $this->bot->sendMessage(
                        $chatId,
                        "❌ Có lỗi xảy ra khi xử lý tin nhắn. Vui lòng thử lại sau."
                    );
                } catch (\Exception $sendError) {
                    Log::error('Failed to send error message: ' . $sendError->getMessage());
                }
            }
        }
    }

    /**
     * Auto-create Location from Telegram group info
     */
    protected function autoCreateLocation(int $chatId, string $chatTitle, string $chatType): Location
    {
        Log::info("Auto-creating location for chat: {$chatTitle} ({$chatId})");

        // Generate unique code
        $code = $this->generateLocationCode($chatTitle);

        // Determine name based on chat type
        if ($chatType === 'private') {
            $name = "Chat riêng - {$chatTitle}";
        } else {
            $name = $chatTitle;
        }

        // Create location
        $location = Location::create([
            'name' => $name,
            'code' => $code,
            'chat_id' => $chatId,
            'is_active' => true,
            'notes' => "Tự động tạo từ Telegram group lúc " . now()->format('d/m/Y H:i'),
        ]);

        Log::info("Location created: {$location->name} ({$location->code})");

        return $location;
    }

    /**
     * Generate unique location code
     */
    protected function generateLocationCode(string $chatTitle): string
    {
        // Remove special chars and get first letters
        $slug = Str::slug($chatTitle);
        $parts = explode('-', $slug);

        // Get first 2-3 letters of first 2 words
        $prefix = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $prefix .= strtoupper(substr($part, 0, 2));
        }

        if (empty($prefix)) {
            $prefix = 'GRP';
        }

        // Add random number to ensure uniqueness
        $code = $prefix . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        // Check if exists, regenerate if needed
        $attempts = 0;
        while (Location::where('code', $code)->exists() && $attempts < 10) {
            $code = $prefix . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $attempts++;
        }

        return $code;
    }

    /**
     * Handle bot commands
     */
    protected function handleCommand(int $chatId, string $command, Location $location): void
    {
        $cmd = explode(' ', $command)[0];

        Log::info("Handling command: {$cmd}");

        switch ($cmd) {
            case '/start':
                $this->sendWelcomeMessage($chatId, $location);
                break;

            case '/status':
                $this->sendStatusReport($chatId, $location);
                break;

            case '/help':
                $this->sendHelpMessage($chatId);
                break;

            case '/info':
                $this->sendLocationInfo($chatId, $location);
                break;

            default:
                $this->bot->sendMessage($chatId,
                    "⚠️ Lệnh không hợp lệ. Gửi /help để xem hướng dẫn."
                );
        }
    }

    /**
     * Check if message indicates completion
     */
    protected function isCompletionReport(string $text): bool
    {
        $keywords = [
            'xong', 'hoàn thành', 'đã làm xong', 'done', 'completed',
            'đã sửa xong', 'đã dọn xong', 'hoàn tất', 'ok xong', 'done rồi',
            'finish', 'finished', 'fixed', 'resolved', 'giải quyết xong'
        ];

        $textLower = strtolower($text);
        foreach ($keywords as $keyword) {
            if (str_contains($textLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle completion report
     */
    protected function handleCompletion(
        int $chatId,
        Location $location,
        string $username,
        ?int $telegramId,
        string $text
    ): void {
        Log::info("Completion report from {$username} at {$location->name}");

        $response = "✅ *ĐÃ NHẬN XÁC NHẬN HOÀN THÀNH*\n\n" .
            "Cảm ơn {$username}! Tiếp tục duy trì chất lượng dịch vụ 5 sao. 🌟";

        $this->bot->sendMessage($chatId, $response, 'Markdown');

        // Save to database
        Report::create([
            'location_id' => $location->id,
            'reporter_name' => $username,
            'reporter_telegram_id' => $telegramId,
            'content' => $text,
            'ai_response' => $response,
            'status' => 'completed',
            'priority' => 'low',
            'completed_at' => now(),
        ]);

        Log::info("Completion report saved for location {$location->id}");
    }

    /**
     * Handle regular report
     */
    protected function handleReport(
        int $chatId,
        Location $location,
        string $username,
        ?int $telegramId,
        ?string $telegramUsername,
        string $text
    ): void {
        Log::info("Regular report from {$username} at {$location->name}");

        // Send processing message
        $this->bot->sendMessage($chatId, "⏳ Tổng Giám Đốc AI đang phân tích báo cáo...");

        try {
            // Get AI response
            $aiResponse = $this->openAI->getCEODirective(
                $location->name,
                $username,
                $text
            );
        } catch (\Exception $e) {
            Log::error("OpenAI error: " . $e->getMessage());
            $aiResponse = "Đã nhận được báo cáo. Hệ thống AI tạm thời quá tải, TGĐ AI sẽ phản hồi trong vòng 15 phút.";
        }

        // Determine priority
        $priority = $this->determinePriority($aiResponse);

        // Extract deadline
        $deadline = $this->extractDeadline($aiResponse);

        // Determine status
        $status = $deadline ? 'in_progress' : 'pending';

        // Save to database
        $report = Report::create([
            'location_id' => $location->id,
            'reporter_name' => $username,
            'reporter_telegram_id' => $telegramId,
            'reporter_username' => $telegramUsername,
            'content' => $text,
            'ai_response' => $aiResponse,
            'status' => $status,
            'priority' => $priority,
            'deadline' => $deadline,
        ]);

        // Send AI response
        $icon = match($priority) {
            'high' => '🔥',
            'medium' => '⚡',
            default => 'ℹ️'
        };

        $message = "{$icon} *CHỈ ĐẠO TGĐ AI:*\n\n{$aiResponse}";

        try {
            $this->bot->sendMessage($chatId, $message, 'Markdown');
        } catch (\Exception $e) {
            Log::error("Failed to send AI response: " . $e->getMessage());
            // Try sending without markdown
            $this->bot->sendMessage($chatId, strip_tags($message));
        }

        Log::info("Report #{$report->id} created successfully");
    }

    /**
     * Determine priority from AI response
     */
    protected function determinePriority(string $response): string
    {
        $responseUpper = strtoupper($response);

        if (str_contains($responseUpper, 'KHẨN') ||
            str_contains($responseUpper, 'GẤP') ||
            str_contains($responseUpper, 'NGAY LẬP TỨC') ||
            str_contains($responseUpper, 'NGAY')) {
            return 'high';
        }

        if (str_contains($responseUpper, 'QUAN TRỌNG') ||
            str_contains($responseUpper, 'ƯU TIÊN') ||
            str_contains($responseUpper, 'CẦN CHÚ Ý')) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Extract deadline from AI response
     */
    protected function extractDeadline(string $text): ?\DateTime
    {
        $patterns = [
            '/trước\s+(\d{1,2})[h:](\d{2})/i',      // trước 15h30
            '/trước\s+(\d{1,2})\s*giờ/i',            // trước 15 giờ
            '/(\d{1,2})[h:](\d{2})/i',               // 15h30
            '/lúc\s+(\d{1,2})[h:](\d{2})/i',        // lúc 15h30
            '/vào\s+(\d{1,2})[h:](\d{2})/i',        // vào 15h30
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $hour = (int) $matches[1];
                $minute = isset($matches[2]) ? (int) $matches[2] : 0;

                // Validate hour and minute
                if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
                    continue;
                }

                $deadline = now()->setTime($hour, $minute, 0);

                // If deadline is in the past, set it to tomorrow
                if ($deadline < now()) {
                    $deadline->addDay();
                }

                return $deadline;
            }
        }

        return null;
    }

    /**
     * Send welcome message
     */
    protected function sendWelcomeMessage(int $chatId, Location $location): void
    {
        $message = "👋 *Chào mừng đến với CEO AI Management System*\n\n" .
            "📍 *Điểm:* {$location->name}\n" .
            "🔢 *Mã:* {$location->code}\n" .
            "🆔 *Chat ID:* {$chatId}\n\n" .
            "✅ *Group này đã được đăng ký tự động!*\n\n" .
            "📝 *Cách sử dụng:*\n" .
            "• Gửi báo cáo bằng cách nhắn tin vào group\n" .
            "• Báo hoàn thành: Gửi tin có từ \"xong\" hoặc \"hoàn thành\"\n\n" .
            "📋 *Commands:*\n" .
            "/status - Xem trạng thái reports\n" .
            "/info - Thông tin điểm\n" .
            "/help - Hướng dẫn chi tiết";

        try {
            $this->bot->sendMessage($chatId, $message, 'Markdown');
        } catch (\Exception $e) {
            Log::error("Failed to send welcome message: " . $e->getMessage());
        }
    }

    /**
     * Send status report
     */
    protected function sendStatusReport(int $chatId, Location $location): void
    {
        $stats = $location->getStatistics();

        $message = "📊 *TRẠNG THÁI {$location->name}*\n\n" .
            "📋 Tổng báo cáo: {$stats['total_reports']}\n" .
            "⏳ Đang chờ: {$stats['pending']}\n" .
            "🔄 Đang xử lý: {$stats['in_progress']}\n" .
            "⚠️ Quá hạn: {$stats['overdue']}\n" .
            "✅ Hoàn thành: {$stats['completed']}\n\n" .
            "📈 Tỷ lệ hoàn thành: {$stats['completion_rate']}%\n" .
            "⏱ Thời gian xử lý TB: " . round($stats['average_processing_time'] ?? 0) . " phút";

        try {
            $this->bot->sendMessage($chatId, $message, 'Markdown');
        } catch (\Exception $e) {
            Log::error("Failed to send status report: " . $e->getMessage());
        }
    }

    /**
     * Send location info
     */
    protected function sendLocationInfo(int $chatId, Location $location): void
    {
        $message = "ℹ️ *THÔNG TIN ĐIỂM KINH DOANH*\n\n" .
            "📍 *Tên:* {$location->name}\n" .
            "🔢 *Mã:* {$location->code}\n" .
            "🆔 *Chat ID:* {$location->chat_id}\n" .
            "📍 *Địa chỉ:* " . ($location->address ?? 'Chưa cập nhật') . "\n" .
            "📞 *Điện thoại:* " . ($location->phone ?? 'Chưa cập nhật') . "\n" .
            "👤 *Quản lý:* " . ($location->manager_name ?? 'Chưa cập nhật') . "\n" .
            "🟢 *Trạng thái:* " . ($location->is_active ? 'Đang hoạt động' : 'Ngưng hoạt động') . "\n\n" .
            "💡 *Để cập nhật thông tin, vào Admin Panel*";

        try {
            $this->bot->sendMessage($chatId, $message, 'Markdown');
        } catch (\Exception $e) {
            Log::error("Failed to send location info: " . $e->getMessage());
        }
    }

    /**
     * Send help message
     */
    protected function sendHelpMessage(int $chatId): void
    {
        $message = "📚 *HƯỚNG DẪN SỬ DỤNG CEO AI BOT*\n\n" .
            "*1️⃣ Gửi báo cáo:*\n" .
            "Chỉ cần nhắn tin bình thường, AI sẽ tự động phân tích và chỉ đạo.\n\n" .
            "*2️⃣ Báo hoàn thành:*\n" .
            "Gửi tin có từ: xong, hoàn thành, đã làm xong, etc.\n\n" .
            "*3️⃣ Commands:*\n" .
            "/start - Xem thông tin chào mừng\n" .
            "/status - Xem trạng thái báo cáo\n" .
            "/info - Xem thông tin điểm\n" .
            "/help - Xem hướng dẫn này\n\n" .
            "*4️⃣ Ví dụ báo cáo:*\n" .
            "• Máy POS lỗi không in được hóa đơn\n" .
            "• Khách phàn nàn về tốc độ phục vụ\n" .
            "• Hôm nay doanh thu 15 triệu\n" .
            "• Đã sửa xong máy lạnh\n\n" .
            "*5️⃣ Tips:*\n" .
            "• Báo cáo càng chi tiết càng tốt\n" .
            "• AI sẽ tự động xác định mức độ ưu tiên\n" .
            "• AI sẽ tự động đặt deadline nếu cần";

        try {
            $this->bot->sendMessage($chatId, $message, 'Markdown');
        } catch (\Exception $e) {
            Log::error("Failed to send help message: " . $e->getMessage());
        }
    }

    /**
     * Set webhook URL
     */
    public static function setWebhook(string $url): array
    {
        $token = '7617448862:AAH7G_WdSzFugy0xqouoxEl1s9xOLy4gwy0';
        $bot = new BotApi($token);

        try {
            $result = $bot->setWebhook($url);
            Log::info("Webhook set successfully", ['url' => $url, 'result' => $result]);
            return ['success' => true, 'result' => $result];
        } catch (\Exception $e) {
            Log::error("Failed to set webhook: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get webhook info
     */
    public static function getWebhookInfo(): array
    {
        $token = '7617448862:AAH7G_WdSzFugy0xqouoxEl1s9xOLy4gwy0';
        $bot = new BotApi($token);

        try {
            $info = $bot->getWebhookInfo();
            return ['success' => true, 'info' => $info];
        } catch (\Exception $e) {
            Log::error("Failed to get webhook info: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete webhook
     */
    public static function deleteWebhook(): array
    {
        $token = '7617448862:AAH7G_WdSzFugy0xqouoxEl1s9xOLy4gwy0';
        $bot = new BotApi($token);

        try {
            $result = $bot->deleteWebhook();
            Log::info("Webhook deleted successfully");
            return ['success' => true, 'result' => $result];
        } catch (\Exception $e) {
            Log::error("Failed to delete webhook: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send custom message to a chat
     */
    public function sendCustomMessage(int $chatId, string $message, string $parseMode = 'Markdown'): bool
    {
        try {
            $this->bot->sendMessage($chatId, $message, $parseMode);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send custom message: " . $e->getMessage());
            return false;
        }
    }
}