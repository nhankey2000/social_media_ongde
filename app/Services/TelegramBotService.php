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
            \Log::info('=== WEBHOOK HANDLER STARTED ===', ['update' => $update]);

            if (!isset($update['message'])) {
                \Log::info('No message in update - SKIPPED');
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

            \Log::info('Message parsed', [
                'chatId' => $chatId,
                'username' => $username,
                'text' => $text
            ]);

            // Find or create location by chat_id
            \Log::info('Looking for location with chat_id: ' . $chatId);
            $location = Location::where('chat_id', $chatId)->first();

            if (!$location) {
                \Log::info('Location NOT FOUND - Creating new...');

                $location = $this->autoCreateLocation($chatId, $chatTitle, $chatType);

                \Log::info('Location created', [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code
                ]);

                $this->sendWelcomeMessage($chatId, $location);
            } else {
                \Log::info('Location FOUND', [
                    'id' => $location->id,
                    'name' => $location->name
                ]);
            }

            // Check for commands
            if (str_starts_with($text, '/')) {
                \Log::info('Processing as COMMAND');
                $this->handleCommand($chatId, $text, $location);
                return;
            }

            // Check if completion report
            if ($this->isCompletionReport($text)) {
                \Log::info('Processing as COMPLETION REPORT');
                $this->handleCompletion($chatId, $location, $username, $telegramId, $text);
                return;
            }

            // Handle regular report
            \Log::info('Processing as REGULAR REPORT');
            $this->handleReport($chatId, $location, $username, $telegramId, $telegramUsername, $text);

            \Log::info('=== WEBHOOK HANDLER COMPLETED ===');

        } catch (\Exception $e) {
            \Log::error('=== WEBHOOK HANDLER ERROR ===');
            \Log::error('Error message: ' . $e->getMessage());
            \Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            if (isset($chatId)) {
                try {
                    $this->bot->sendMessage(
                        $chatId,
                        "❌ Có lỗi xảy ra: " . $e->getMessage()
                    );
                } catch (\Exception $sendError) {
                    \Log::error('Failed to send error message: ' . $sendError->getMessage());
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

        $code = $this->generateLocationCode($chatTitle);

        if ($chatType === 'private') {
            $name = "Chat riêng - {$chatTitle}";
        } else {
            $name = $chatTitle;
        }

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
        $slug = Str::slug($chatTitle);
        $parts = explode('-', $slug);

        $prefix = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $prefix .= strtoupper(substr($part, 0, 2));
        }

        if (empty($prefix)) {
            $prefix = 'GRP';
        }

        $code = $prefix . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

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
        \Log::info('=== HANDLE REPORT START ===');
        \Log::info("Report from {$username} at {$location->name}");

        // Send processing message
        try {
            \Log::info('Sending processing message...');
            $this->bot->sendMessage($chatId, "⏳ Tổng Giám Đốc AI đang phân tích báo cáo...");
            \Log::info('Processing message sent ✓');
        } catch (\Exception $e) {
            \Log::error('Failed to send processing message: ' . $e->getMessage());
        }

        // Call AI
        try {
            \Log::info('Calling AI...');
            $aiResult = $this->openAI->getCEODirective(
                $location->name,
                $username,
                $text
            );

            // Xử lý kết quả từ AI (có thể là string hoặc array)
            if (is_array($aiResult)) {
                $aiResponse = $aiResult['directive'];
                $isFinancial = $aiResult['is_financial'] ?? false;
                $needsChairmanApproval = $aiResult['needs_chairman_approval'] ?? false;
            } else {
                $aiResponse = $aiResult;
                $isFinancial = $this->detectFinancialIssue($text);
                $needsChairmanApproval = $isFinancial;
            }

            \Log::info('AI response received ✓', [
                'is_financial' => $isFinancial,
                'needs_approval' => $needsChairmanApproval
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get AI directive: ' . $e->getMessage());

            $this->bot->sendMessage(
                $chatId,
                "⚠️ Xin lỗi, hệ thống đang gặp sự cố. Vui lòng thử lại sau.\n\nLỗi: " . $e->getMessage()
            );
            return;
        }

        // Determine priority
        $priority = $this->determinePriority($aiResponse);
        if ($isFinancial) {
            $priority = 'high'; // Vấn đề tài chính luôn là high priority
        }
        \Log::info('Priority determined: ' . $priority);

        // Extract deadline
        $deadline = $this->extractDeadline($aiResponse);
        \Log::info('Deadline extracted: ' . ($deadline ? $deadline->toDateTimeString() : 'null'));

        // Determine status
        $status = $needsChairmanApproval ? 'pending_approval' : ($deadline ? 'in_progress' : 'pending');
        \Log::info('Status set: ' . $status);

        // Save to database
        try {
            \Log::info('Saving report to database...');
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
            \Log::info('Report saved ✓', ['report_id' => $report->id]);

        } catch (\Exception $e) {
            \Log::error('Failed to save report: ' . $e->getMessage());
            \Log::error('SQL Error: ' . $e->getTraceAsString());
            throw $e;
        }

        // === 1️⃣ GỬI TIN NHẮN VÀO GROUP ===
        $icon = match($priority) {
            'high' => '🔥',
            'medium' => '⚡',
            default => 'ℹ️'
        };

        $groupMessage = "{$icon} *CHỈ ĐẠO TGĐ AI:*\n\n{$aiResponse}";

        try {
            \Log::info('Sending AI response to group...');
            $this->bot->sendMessage($chatId, $groupMessage, 'Markdown');
            \Log::info('Group message sent ✓');
        } catch (\Exception $e) {
            \Log::error("Failed to send group message: " . $e->getMessage());
            try {
                $this->bot->sendMessage($chatId, strip_tags($groupMessage));
            } catch (\Exception $e2) {
                \Log::error("Failed to send plain text: " . $e2->getMessage());
            }
        }

        // === 2️⃣ GỬI BẢN SAO CHO ADMIN/CHỦ TỊCH ===
        $this->sendReportToAdmin($report, $location, $username, $text, $aiResponse, $priority, $isFinancial, $needsChairmanApproval);

        \Log::info('=== HANDLE REPORT COMPLETED ===');
    }

    /**
     * Phát hiện vấn đề liên quan tài chính
     */
    protected function detectFinancialIssue(string $text): bool
    {
        $financialKeywords = [
            'tiền', 'đồng', 'vnd', 'triệu', 'nghìn', 'tỷ', 'ngàn',
            'chi phí', 'kinh phí', 'ngân sách', 'budget',
            'mua', 'sắm', 'đặt hàng', 'order', 'thanh toán', 'payment',
            'hóa đơn', 'invoice', 'chi', 'trả tiền',
            'đầu tư', 'invest', 'trang thiết bị', 'thiết bị mới',
            'nâng cấp', 'upgrade', 'bổ sung',
            'tuyển', 'recruitment', 'lương', 'salary', 'thưởng', 'bonus',
            'tăng lương', 'phụ cấp', 'trợ cấp',
            'phê duyệt', 'approval', 'xin phép', 'cần tiền',
            'hết tiền', 'thiếu tiền', 'cần mua'
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
     * Gửi bản sao báo cáo cho Admin/Chủ Tịch
     */
    protected function sendReportToAdmin($report, $location, $username, $text, $aiResponse, $priority, $isFinancial = false, $needsApproval = false)
    {
        try {
            // ID Telegram của Chủ Tịch Lê Hải Phúc
            $adminTelegramId = env('TELEGRAM_ADMIN_ID', 6884007048);

            $priorityIcon = match($priority) {
                'high' => '🔥',
                'medium' => '⚡',
                default => 'ℹ️'
            };

            $financialBadge = $isFinancial ? "\n💰 *[VẤN ĐỀ TÀI CHÍNH - CẦN CHỦ TỊCH LÊ HẢI PHÚC QUYẾT ĐỊNH]*" : "";
            $approvalNote = $needsApproval ? "\n\n⚠️ *Vấn đề này cần Chủ Tịch phê duyệt trước khi thực hiện!*" : "";

            $adminMessage = "📊 *BÁO CÁO MỚI TỪ HỆ THỐNG*{$financialBadge}\n\n" .
                "━━━━━━━━━━━━━━━━━━\n" .
                "🆔 *Report ID:* #{$report->id}\n" .
                "📍 *Điểm:* {$location->name}\n" .
                "👤 *Người báo:* {$username}\n" .
                "📅 *Thời gian:* " . now()->format('d/m/Y H:i:s') . "\n" .
                "{$priorityIcon} *Mức độ:* " . strtoupper($priority) . "\n\n" .
                "📋 *NỘI DUNG:*\n_{$text}_\n\n" .
                "🤖 *CHỈ ĐẠO TGĐ AI:*\n{$aiResponse}{$approvalNote}\n\n" .
                "━━━━━━━━━━━━━━━━━━";

            $this->bot->sendMessage($adminTelegramId, $adminMessage, 'Markdown');

            \Log::info('Admin notification sent', [
                'report_id' => $report->id,
                'admin_id' => $adminTelegramId,
                'is_financial' => $isFinancial
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to notify admin: ' . $e->getMessage());
        }
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
            '/trước\s+(\d{1,2})[h:](\d{2})/i',
            '/trước\s+(\d{1,2})\s*giờ/i',
            '/(\d{1,2})[h:](\d{2})/i',
            '/lúc\s+(\d{1,2})[h:](\d{2})/i',
            '/vào\s+(\d{1,2})[h:](\d{2})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $hour = (int) $matches[1];
                $minute = isset($matches[2]) ? (int) $matches[2] : 0;

                if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
                    continue;
                }

                $deadline = now()->setTime($hour, $minute, 0);

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