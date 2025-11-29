<?php

namespace App\Services;

use TelegramBot\Api\BotApi;
use App\Models\Location;
use App\Models\Report;
use App\Models\TelegramMember;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramBotService
{
    protected BotApi $bot;
    protected OpenAIService $openAI;
    protected ?TelegramMemberService $memberService = null;
    protected ?TaskAssignmentService $taskService = null;

    public function __construct()
    {
        // Hardcode token để chạy ngay
        $this->bot = new BotApi('7617448862:AAH7G_WdSzFugy0xqouoxEl1s9xOLy4gwy0');
        $this->openAI = new OpenAIService();

        // Lazy load services để tránh lỗi nếu chưa cài đặt
        try {
            $this->memberService = app(TelegramMemberService::class);
            $this->taskService = app(TaskAssignmentService::class);
        } catch (\Exception $e) {
            Log::warning('Auto-assignment services not available: ' . $e->getMessage());
        }
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function handleWebhook(array $update): void
    {
        try {
            Log::info('=== WEBHOOK HANDLER STARTED ===', ['update' => $update]);

            if (!isset($update['message'])) {
                Log::info('No message in update - SKIPPED');
                return;
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $chatTitle = $message['chat']['title'] ?? 'Private Chat';
            $chatType = $message['chat']['type'] ?? 'private';
            $text = $message['text'] ?? '';
            $from = $message['from'];

            // Lấy tên đầy đủ
            $firstName = $from['first_name'] ?? '';
            $lastName = $from['last_name'] ?? '';
            $username = trim($firstName . ' ' . $lastName) ?: 'Unknown';

            $telegramId = $from['id'] ?? null;
            $telegramUsername = $from['username'] ?? null;

            Log::info('Message parsed', [
                'chatId' => $chatId,
                'username' => $username,
                'text' => $text
            ]);

            // Find or create location by chat_id
            Log::info('Looking for location with chat_id: ' . $chatId);
            $location = Location::where('chat_id', $chatId)->first();

            if (!$location) {
                Log::info('Location NOT FOUND - Creating new...');
                $location = $this->autoCreateLocation($chatId, $chatTitle, $chatType);

                Log::info('Location created', [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code
                ]);

                $this->sendWelcomeMessage($chatId, $location);

                // Auto-sync members khi tạo location mới (nếu có service)
                if ($this->memberService) {
                    try {
                        $this->memberService->syncGroupMembers($location);
                    } catch (\Exception $e) {
                        Log::warning('Failed to auto-sync members: ' . $e->getMessage());
                    }
                }
            } else {
                Log::info('Location FOUND', [
                    'id' => $location->id,
                    'name' => $location->name
                ]);
            }

            // Cập nhật/tạo member từ tin nhắn (nếu có service)
            $member = null;
            if ($this->memberService) {
                try {
                    $member = $this->memberService->updateMemberFromMessage($location, $from);
                    Log::info("Member updated: {$member->full_name} (" . ($member->role ?? 'no role') . ")");
                } catch (\Exception $e) {
                    Log::warning('Failed to update member: ' . $e->getMessage());
                }
            }

            // Check for commands
            if (str_starts_with($text, '/')) {
                Log::info('Processing as COMMAND');
                $this->handleCommand($chatId, $text, $location, $member);
                return;
            }

            // Check for pending completion confirmation
            if ($member && $this->taskService) {
                $pendingAssignmentId = \Cache::get("pending_completion_{$member->id}");
                if ($pendingAssignmentId && $this->isConfirmation($text)) {
                    Log::info('Processing as CONFIRMATION');
                    $this->handleConfirmation($chatId, $member, $pendingAssignmentId, $text);
                    return;
                }
            }

            // Check if acknowledgment ("Nhận việc")
            if ($this->isAcknowledgment($text) && $member && $this->taskService) {
                Log::info('Processing as ACKNOWLEDGMENT');
                $this->handleAcknowledgment($chatId, $location, $member);
                return;
            }

            // Check if completion report
            if ($this->isCompletionReport($text)) {
                Log::info('Processing as COMPLETION REPORT');
                $this->handleCompletion($chatId, $location, $username, $telegramId, $text, $member);
                return;
            }

            // Handle regular report
            Log::info('Processing as REGULAR REPORT');
            $this->handleReport($chatId, $location, $username, $telegramId, $telegramUsername, $text);

            Log::info('=== WEBHOOK HANDLER COMPLETED ===');

        } catch (\Exception $e) {
            Log::error('=== WEBHOOK HANDLER ERROR ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if (isset($chatId)) {
                try {
                    $this->bot->sendMessage(
                        $chatId,
                        "❌ Có lỗi xảy ra: " . $e->getMessage()
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
    protected function handleCommand(int $chatId, string $command, Location $location, ?TelegramMember $member = null): void
    {
        $cmd = explode(' ', $command)[0];

        Log::info("Handling command: {$cmd}");

        switch ($cmd) {
            case '/start':
                $this->sendWelcomeMessage($chatId, $location);
                break;

            case '/sync':
                $this->handleSyncMembers($chatId, $location);
                break;

            case '/members':
                $this->handleListMembers($chatId, $location);
                break;

            case '/mytasks':
                if ($member) {
                    $this->handleMyTasks($chatId, $member);
                } else {
                    $this->bot->sendMessage($chatId, "⚠️ Không tìm thấy thông tin member của bạn.");
                }
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
     * Check if message is acknowledgment
     */
    protected function isAcknowledgment(string $text): bool
    {
        $keywords = ['nhận việc', 'ok nhận', 'đã nhận', 'received', 'accept', 'nhận', 'oke nhận'];
        $textLower = mb_strtolower($text);

        foreach ($keywords as $keyword) {
            if (str_contains($textLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if message is confirmation (Có/Không)
     */
    protected function isConfirmation(string $text): bool
    {
        $textLower = mb_strtolower(trim($text));

        $confirmKeywords = [
            'có', 'yes', 'đúng', 'ok', 'oke', 'được',
            'rồi', 'đúng rồi', 'không', 'no', 'sai',
            'chưa', 'không phải'
        ];

        foreach ($confirmKeywords as $keyword) {
            if ($textLower === $keyword || $textLower === $keyword . '!') {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle completion confirmation
     */
    protected function handleConfirmation(
        int $chatId,
        TelegramMember $member,
        int $assignmentId,
        string $text
    ): void {
        $textLower = mb_strtolower(trim($text));

        $assignment = \App\Models\TaskAssignment::find($assignmentId);

        if (!$assignment) {
            \Cache::forget("pending_completion_{$member->id}");
            $this->bot->sendMessage(
                $chatId,
                "❌ Không tìm thấy công việc cần xác nhận.",
                'Markdown'
            );
            return;
        }

        // Check positive confirmation
        $positiveKeywords = ['có', 'yes', 'đúng', 'ok', 'oke', 'được', 'rồi', 'đúng rồi'];
        $isPositive = false;

        foreach ($positiveKeywords as $keyword) {
            if ($textLower === $keyword || $textLower === $keyword . '!') {
                $isPositive = true;
                break;
            }
        }

        if ($isPositive) {
            // Xác nhận đúng → Complete task
            \Cache::forget("pending_completion_{$member->id}");
            $this->taskService->completeTask($assignment->report, $member, $chatId);
        } else {
            // Xác nhận sai → Hủy và hỏi lại
            \Cache::forget("pending_completion_{$member->id}");

            $activeAssignments = $member->taskAssignments()
                ->whereIn('status', ['assigned', 'acknowledged'])
                ->with('report')
                ->orderBy('assigned_at', 'desc')
                ->get();

            $response = "❌ *ĐÃ HỦY XÁC NHẬN*\n\n";

            if ($activeAssignments->count() > 0) {
                $response .= "Bạn đang có {$activeAssignments->count()} công việc:\n\n";

                foreach ($activeAssignments as $index => $asg) {
                    $taskNumber = $index + 1;
                    $taskDesc = $this->extractTaskDescription($asg->report->content);
                    $response .= "{$taskNumber}. {$taskDesc}\n";
                }

                $response .= "\n💡 Vui lòng nói rõ: \"Xong [tên công việc]\"";
            } else {
                $response .= "Bạn không có công việc nào đang làm.";
            }

            $this->bot->sendMessage($chatId, $response, 'Markdown');
        }
    }

    /**
     * Handle acknowledgment
     */
    protected function handleAcknowledgment(int $chatId, Location $location, TelegramMember $member): void
    {
        if (!$this->taskService) {
            return;
        }

        // Tìm task gần nhất được giao cho member này
        $latestAssignment = $member->taskAssignments()
            ->where('status', 'assigned')
            ->latest('assigned_at')
            ->first();

        if ($latestAssignment) {
            $this->taskService->acknowledgeTask($latestAssignment->report, $member, $chatId);
        } else {
            $this->bot->sendMessage($chatId, "ℹ️ Không tìm thấy việc cần xác nhận.");
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

        $textLower = mb_strtolower($text);
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
        string $text,
        ?TelegramMember $member = null
    ): void {
        Log::info("Completion report from {$username} at {$location->name}");

        Log::info('handleCompletion - Start', [
            'has_member' => $member !== null,
            'has_taskService' => $this->taskService !== null,
            'member_id' => $member?->id,
            'text' => $text
        ]);

        // Nếu có member và task service, xử lý completion cho task
        if ($member && $this->taskService) {
            Log::info('handleCompletion - Has member and taskService, checking assignments');

            $activeAssignments = $member->taskAssignments()
                ->whereIn('status', ['assigned', 'acknowledged'])
                ->with('report')
                ->orderBy('assigned_at', 'desc')
                ->get();

            Log::info('handleCompletion - Active assignments found', [
                'count' => $activeAssignments->count(),
                'assignments' => $activeAssignments->map(fn($a) => [
                    'id' => $a->id,
                    'report_id' => $a->report_id,
                    'status' => $a->status
                ])
            ]);

            if ($activeAssignments->count() > 1) {
                Log::info('handleCompletion - Multiple tasks detected, checking match');

                // Có nhiều tasks → Check xem user có nói rõ task nào không
                $textLower = mb_strtolower($text);
                $matchedAssignment = null;
                $bestMatchScore = 0;

                foreach ($activeAssignments as $assignment) {
                    $taskDesc = $this->extractTaskDescription($assignment->report->content);
                    $taskDescLower = mb_strtolower($taskDesc);

                    // Extract keywords từ task description
                    $taskKeywords = preg_split('/[\s,.:;!?]+/', $taskDescLower);
                    $taskKeywords = array_filter($taskKeywords, fn($w) => mb_strlen($w) > 2);

                    // Count matching keywords
                    $matchCount = 0;
                    foreach ($taskKeywords as $keyword) {
                        if (str_contains($textLower, $keyword)) {
                            $matchCount++;
                        }
                    }

                    Log::info('handleCompletion - Task match check', [
                        'task_desc' => $taskDesc,
                        'match_count' => $matchCount,
                        'keywords' => $taskKeywords
                    ]);

                    if ($matchCount > $bestMatchScore) {
                        $bestMatchScore = $matchCount;
                        $matchedAssignment = $assignment;
                    }
                }

                Log::info('handleCompletion - Best match result', [
                    'best_score' => $bestMatchScore,
                    'matched_id' => $matchedAssignment?->id
                ]);

                // Nếu match được task cụ thể → Complete ngay
                if ($bestMatchScore >= 2) {
                    Log::info('handleCompletion - Match score >= 2, completing task');
                    $this->taskService->completeTask($matchedAssignment->report, $member, $chatId);
                    return;
                }

                // Nếu không match → Hỏi lại
                Log::info('handleCompletion - No strong match, asking for clarification');
                $response = "⚠️ *BẠN CÓ {$activeAssignments->count()} CÔNG VIỆC ĐANG LÀM*\n\n";
                $response .= "Vui lòng cho biết cụ thể xong công việc nào:\n\n";

                foreach ($activeAssignments as $index => $assignment) {
                    $taskNumber = $index + 1;
                    $taskDesc = $this->extractTaskDescription($assignment->report->content);
                    $response .= "{$taskNumber}. {$taskDesc}\n";
                }

                $response .= "\n💡 *Hướng dẫn:*\n";
                $response .= "Trả lời: \"Xong [mô tả công việc]\"\n";
                $response .= "Ví dụ: \"Xong sửa máy tính\" hoặc \"Đã sửa xong máy POS\"";

                $this->bot->sendMessage($chatId, $response, 'Markdown');
                Log::info('handleCompletion - Sent clarification request');
                return;
            }

            if ($activeAssignments->count() === 1) {
                Log::info('handleCompletion - Single task detected');

                // Chỉ có 1 task → Xác nhận và hoàn thành
                $assignment = $activeAssignments->first();
                $taskDesc = $this->extractTaskDescription($assignment->report->content);

                // Check xem có match với task description không
                $textLower = mb_strtolower($text);
                $taskDescLower = mb_strtolower($taskDesc);

                // Extract keywords từ task description
                $taskKeywords = preg_split('/[\s,.:;!?]+/', $taskDescLower);
                $taskKeywords = array_filter($taskKeywords, fn($w) => mb_strlen($w) > 3);

                // Check xem user có nhắc đến task keywords không
                $mentioned = false;
                foreach ($taskKeywords as $keyword) {
                    if (str_contains($textLower, $keyword)) {
                        $mentioned = true;
                        break;
                    }
                }

                Log::info('handleCompletion - Single task check', [
                    'mentioned' => $mentioned,
                    'text_length' => mb_strlen($text),
                    'keywords' => $taskKeywords
                ]);

                if (!$mentioned && mb_strlen($text) < 20) {
                    // User chỉ nói "xong" không rõ ràng → Xác nhận
                    Log::info('handleCompletion - Asking for confirmation');

                    $response = "📋 *XÁC NHẬN HOÀN THÀNH*\n\n";
                    $response .= "Bạn đã hoàn thành công việc:\n";
                    $response .= "✅ *{$taskDesc}*\n\n";
                    $response .= "Xác nhận đúng không? (Có/Không)";

                    $this->bot->sendMessage($chatId, $response, 'Markdown');

                    // Lưu pending confirmation (có thể dùng cache hoặc session)
                    \Cache::put(
                        "pending_completion_{$member->id}",
                        $assignment->id,
                        now()->addMinutes(5)
                    );

                    Log::info('handleCompletion - Saved pending confirmation', [
                        'assignment_id' => $assignment->id
                    ]);
                    return;
                }

                // Hoàn thành task
                Log::info('handleCompletion - Auto-completing single task');
                $this->taskService->completeTask($assignment->report, $member, $chatId);
                return;
            }

            Log::info('handleCompletion - No active assignments found');
        } else {
            Log::info('handleCompletion - No member or taskService', [
                'has_member' => $member !== null,
                'has_taskService' => $this->taskService !== null
            ]);
        }

        // Xử lý completion thông thường (không có task cụ thể)
        Log::info('handleCompletion - Processing as generic completion');

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
     * Extract short task description from report content
     */
    protected function extractTaskDescription(string $content): string
    {
        // Lấy 50 ký tự đầu hoặc câu đầu tiên
        $content = trim($content);

        // Tìm dấu chấm câu đầu tiên
        $endPos = mb_strpos($content, '.');
        if ($endPos !== false && $endPos < 100) {
            return mb_substr($content, 0, $endPos);
        }

        // Nếu không có dấu chấm, lấy 60 ký tự
        return mb_strlen($content) > 60
            ? mb_substr($content, 0, 60) . '...'
            : $content;
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
        Log::info('=== HANDLE REPORT START ===');
        Log::info("Report from {$username} at {$location->name}");

        // Send processing message
        try {
            Log::info('Sending processing message...');
            $this->bot->sendMessage($chatId, "⏳ Tổng Giám Đốc AI đang phân tích báo cáo...");
            Log::info('Processing message sent ✓');
        } catch (\Exception $e) {
            Log::error('Failed to send processing message: ' . $e->getMessage());
        }

        // Call AI
        try {
            Log::info('Calling AI...');
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

            Log::info('AI response received ✓', [
                'is_financial' => $isFinancial,
                'needs_approval' => $needsChairmanApproval
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get AI directive: ' . $e->getMessage());

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
        Log::info('Priority determined: ' . $priority);

        // Extract deadline
        $deadline = $this->extractDeadline($aiResponse);
        Log::info('Deadline extracted: ' . ($deadline ? $deadline->toDateTimeString() : 'null'));

        // Determine status
        $status = $deadline ? 'in_progress' : 'pending';
        Log::info('Status set: ' . $status);

        // Save to database
        try {
            Log::info('Saving report to database...');
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
            Log::info('Report saved ✓', ['report_id' => $report->id]);

        } catch (\Exception $e) {
            Log::error('Failed to save report: ' . $e->getMessage());
            Log::error('SQL Error: ' . $e->getTraceAsString());
            throw $e;
        }

        // === 1️⃣ GỬI CHỈ ĐẠO TGĐ AI VÀO GROUP ===
        $icon = match($priority) {
            'high' => '🔥',
            'medium' => '⚡',
            default => 'ℹ️'
        };

        $groupMessage = "{$icon} *CHỈ ĐẠO TGĐ AI:*\n\n{$aiResponse}";

        try {
            Log::info('Sending AI response to group...');
            $this->bot->sendMessage($chatId, $groupMessage, 'Markdown');
            Log::info('Group message sent ✓');
        } catch (\Exception $e) {
            Log::error("Failed to send group message: " . $e->getMessage());
            try {
                $this->bot->sendMessage($chatId, strip_tags($groupMessage));
            } catch (\Exception $e2) {
                Log::error("Failed to send plain text: " . $e2->getMessage());
            }
        }

        // === 2️⃣ TỰ ĐỘNG GIAO VIỆC (nếu không phải vấn đề tài chính) ===
        if (!$isFinancial && $this->memberService && $this->taskService) {
            try {
                $assignmentResult = $this->taskService->autoAssignTasks($report, $location);

                if ($assignmentResult['assigned']) {
                    Log::info("Auto-assigned to {$assignmentResult['count']} members");

                    // Gửi tóm tắt giao việc
                    $summary = "\n📊 *ĐÃ GIAO VIỆC CHO:*\n";
                    foreach ($assignmentResult['members'] as $item) {
                        $summary .= "• {$item['member']} ({$item['role']})\n";
                    }

                    try {
                        $this->bot->sendMessage($chatId, $summary, 'Markdown');
                    } catch (\Exception $e) {
                        Log::error("Failed to send assignment summary: " . $e->getMessage());
                    }
                } else {
                    Log::info("No auto-assignment: " . ($assignmentResult['reason'] ?? 'Unknown reason'));
                }
            } catch (\Exception $e) {
                Log::error("Auto-assignment failed: " . $e->getMessage());
            }
        }

        // === 3️⃣ GỬI BẢN SAO CHO ADMIN/CHỦ TỊCH ===
        $this->sendReportToAdmin($report, $location, $username, $text, $aiResponse, $priority, $isFinancial, $needsChairmanApproval);

        Log::info('=== HANDLE REPORT COMPLETED ===');
    }

    /**
     * Handle /sync command - Sync group members
     */
    protected function handleSyncMembers(int $chatId, Location $location): void
    {
        if (!$this->memberService) {
            $this->bot->sendMessage($chatId, "⚠️ Tính năng này chưa được kích hoạt.");
            return;
        }

        $this->bot->sendMessage($chatId, "🔄 Đang quét danh sách thành viên...");

        $result = $this->memberService->syncGroupMembers($location);

        if ($result['success']) {
            $stats = $result['stats'];
            $message = "✅ *HOÀN TẤT QUÉT MEMBERS*\n\n" .
                "📊 Thống kê:\n" .
                "• Mới: {$stats['new']}\n" .
                "• Cập nhật: {$stats['updated']}\n" .
                "• Tổng: {$stats['total']}\n\n" .
                "👥 Danh sách:\n";

            foreach (array_slice($result['members'], 0, 10) as $m) {
                $badge = $m['status'] === 'new' ? '🆕' : '🔄';
                $message .= "{$badge} {$m['name']} - {$m['role']}\n";
            }

            if (count($result['members']) > 10) {
                $message .= "\n... và " . (count($result['members']) - 10) . " người khác";
            }

            $this->bot->sendMessage($chatId, $message, 'Markdown');
        } else {
            $this->bot->sendMessage($chatId, "❌ Lỗi: " . $result['error']);
        }
    }

    /**
     * Handle /members command - List all members
     */
    protected function handleListMembers(int $chatId, Location $location): void
    {
        if (!$this->memberService) {
            $this->bot->sendMessage($chatId, "⚠️ Tính năng này chưa được kích hoạt.");
            return;
        }

        $members = TelegramMember::where('location_id', $location->id)
            ->where('is_active', true)
            ->get();

        if ($members->isEmpty()) {
            $this->bot->sendMessage($chatId, "ℹ️ Chưa có thành viên nào. Gửi /sync để quét.");
            return;
        }

        $message = "👥 *DANH SÁCH THÀNH VIÊN*\n\n";

        $byRole = $members->groupBy('role');
        foreach ($byRole as $role => $roleMembers) {
            $roleName = $role ?? 'Chưa xác định';
            $message .= "*{$roleName}:*\n";
            foreach ($roleMembers as $m) {
                $message .= "• {$m->full_name}\n";
            }
            $message .= "\n";
        }

        $this->bot->sendMessage($chatId, $message, 'Markdown');
    }

    /**
     * Handle /mytasks command - Show user's tasks
     */
    protected function handleMyTasks(int $chatId, TelegramMember $member): void
    {
        if (!$this->taskService) {
            $this->bot->sendMessage($chatId, "⚠️ Tính năng này chưa được kích hoạt.");
            return;
        }

        $tasks = $member->getActiveTasks();

        if ($tasks->isEmpty()) {
            $this->bot->sendMessage($chatId, "✅ Bạn không có việc đang chờ xử lý.");
            return;
        }

        $message = "📋 *VIỆC CỦA BẠN*\n\n";

        foreach ($tasks as $task) {
            $report = $task->report;
            $status = $task->status === 'assigned' ? '🆕 Mới' : '✅ Đã nhận';

            $message .= "*Report #{$report->id}*\n" .
                "Status: {$status}\n" .
                "📝 {$task->task_description}\n" .
                "⏰ Giao lúc: " . $task->assigned_at->format('H:i d/m/Y') . "\n";

            if ($report->deadline) {
                $message .= "⏳ Deadline: " . $report->deadline->format('H:i d/m/Y') . "\n";
            }

            $message .= "\n";
        }

        $this->bot->sendMessage($chatId, $message, 'Markdown');
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

            Log::info('Admin notification sent', [
                'report_id' => $report->id,
                'admin_id' => $adminTelegramId,
                'is_financial' => $isFinancial
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to notify admin: ' . $e->getMessage());
        }
    }

    /**
     * Determine priority from AI response
     */
    protected function determinePriority(string $response): string
    {
        $responseUpper = mb_strtoupper($response);

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
            "• Báo hoàn thành: Gửi tin có từ \"xong\" hoặc \"hoàn thành\"\n" .
            "• Nhận việc: Gửi \"Nhận việc\" khi được giao task\n\n" .
            "📋 *Commands:*\n" .
            "/sync - Quét danh sách thành viên\n" .
            "/members - Xem danh sách members\n" .
            "/mytasks - Xem việc của tôi\n" .
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
            "*2️⃣ Nhận việc:*\n" .
            "Khi được giao việc, reply \"Nhận việc\" để xác nhận.\n\n" .
            "*3️⃣ Báo hoàn thành:*\n" .
            "Gửi tin có từ: xong, hoàn thành, đã làm xong, etc.\n\n" .
            "*4️⃣ Commands:*\n" .
            "/start - Xem thông tin chào mừng\n" .
            "/sync - Quét members trong group\n" .
            "/members - Xem danh sách members\n" .
            "/mytasks - Xem việc của tôi\n" .
            "/status - Xem trạng thái báo cáo\n" .
            "/info - Xem thông tin điểm\n" .
            "/help - Xem hướng dẫn này\n\n" .
            "*5️⃣ Ví dụ báo cáo:*\n" .
            "• Máy POS lỗi không in được hóa đơn\n" .
            "• Khách phàn nàn về tốc độ phục vụ\n" .
            "• Hôm nay doanh thu 15 triệu\n" .
            "• Đã sửa xong máy lạnh\n\n" .
            "*6️⃣ Tự động giao việc:*\n" .
            "• Bot sẽ tự động giao việc cho đúng người\n" .
            "• Dựa trên vai trò và từ khóa\n" .
            "• VD: \"Máy POS lỗi\" → giao cho IT\n\n" .
            "*7️⃣ Tips:*\n" .
            "• Đặt tên có vai trò (VD: Tân Bảo Trì, Nhân IT)\n" .
            "• Chạy /sync để cập nhật members\n" .
            "• Báo cáo càng chi tiết càng tốt";

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