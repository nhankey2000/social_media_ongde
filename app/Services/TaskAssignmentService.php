<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Location;
use App\Models\TelegramMember;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;

class TaskAssignmentService
{
    protected BotApi $bot;
    protected TelegramMemberService $memberService;

    public function __construct(TelegramMemberService $memberService)
    {
        $token = env('TELEGRAM_BOT_TOKEN', '7617448862:AAH7G_WdSzFugy0xqouoxEl1s9xOLy4gwy0');
        $this->bot = new BotApi($token);
        $this->memberService = $memberService;
    }

    /**
     * Tự động giao việc dựa trên nội dung báo cáo
     */
    public function autoAssignTasks(Report $report, Location $location): array
    {
        Log::info("Auto-assigning tasks for report #{$report->id}");

        // Tìm members phù hợp
        $relevantMembers = $this->memberService->findRelevantMembers(
            $location,
            $report->content,
            3
        );

        if (empty($relevantMembers)) {
            Log::info("No relevant members found for report #{$report->id}");
            return [
                'assigned' => false,
                'reason' => 'Không tìm thấy thành viên phù hợp',
                'members' => []
            ];
        }

        $assignments = [];
        $chatId = $location->chat_id;

        foreach ($relevantMembers as $item) {
            $member = $item['member'];
            $score = $item['score'];
            $role = $item['role'];

            // Tạo task description
            $taskDescription = $this->generateTaskDescription($report, $role);

            // Lưu assignment vào database
            $assignment = TaskAssignment::create([
                'report_id' => $report->id,
                'telegram_member_id' => $member->id,
                'task_description' => $taskDescription,
                'assigned_at' => now(),
                'status' => 'assigned'
            ]);

            // Gửi thông báo mention trong group
            $this->sendAssignmentNotification($chatId, $member, $report, $taskDescription);

            $assignments[] = [
                'member' => $member->full_name,
                'role' => $role,
                'score' => $score,
                'task' => $taskDescription
            ];

            Log::info("Task assigned to {$member->full_name} (score: {$score})");
        }

        return [
            'assigned' => true,
            'count' => count($assignments),
            'members' => $assignments
        ];
    }

    /**
     * Tạo task description dựa trên vai trò
     */
    protected function generateTaskDescription(Report $report, string $role): string
    {
        $content = $report->content;
        $deadline = $report->deadline;

        $taskTemplates = [
            'IT' => "🖥 Kiểm tra và xử lý vấn đề kỹ thuật",
            'Bảo trì' => "🔧 Kiểm tra và sửa chữa thiết bị",
            'Kế toán' => "💰 Xử lý vấn đề tài chính",
            'Phục vụ' => "👥 Cải thiện chất lượng dịch vụ khách hàng",
            'Bếp' => "🍳 Xử lý vấn đề liên quan đến bếp và thực phẩm",
            'Lễ tân' => "📞 Hỗ trợ khách hàng và xử lý phản hồi",
        ];

        $template = $taskTemplates[$role] ?? "✅ Xử lý vấn đề được báo cáo";

        if ($deadline) {
            $template .= "\n⏰ Deadline: " . $deadline->format('H:i d/m/Y');
        }

        return $template;
    }

    /**
     * Gửi thông báo giao việc trong group
     */
    protected function sendAssignmentNotification(
        int $chatId,
        TelegramMember $member,
        Report $report,
        string $taskDescription
    ): void {
        try {
            $mention = $member->mention;
            $priority = $this->getPriorityIcon($report->priority);

            $message = "{$priority} *GIAO VIỆC*\n\n" .
                "👤 Người nhận: {$mention}\n" .
                "🎯 Vai trò: *{$member->role}*\n\n" .
                "📋 *Nhiệm vụ:*\n{$taskDescription}\n\n" .
                "📝 *Chi tiết:*\n_{$report->content}_\n\n";

            if ($report->deadline) {
                $message .= "⏰ *Deadline:* {$report->deadline->format('H:i d/m/Y')}\n\n";
            }

            $message .= "💬 Trả lời \"Nhận việc\" để xác nhận\n" .
                "✅ Trả lời \"Xong\" khi hoàn thành";

            $this->bot->sendMessage($chatId, $message, 'Markdown');

        } catch (\Exception $e) {
            Log::error("Failed to send assignment notification: " . $e->getMessage());

            // Fallback: Gửi tin nhắn không có markdown
            try {
                $plainMessage = "GIAO VIỆC\n\n" .
                    "Người nhận: {$member->full_name}\n" .
                    "Vai trò: {$member->role}\n\n" .
                    "Chi tiết: {$report->content}";

                $this->bot->sendMessage($chatId, $plainMessage);
            } catch (\Exception $e2) {
                Log::error("Fallback message also failed: " . $e2->getMessage());
            }
        }
    }

    /**
     * Xử lý khi member xác nhận nhận việc
     */
    public function acknowledgeTask(Report $report, TelegramMember $member, int $chatId): void
    {
        $assignment = TaskAssignment::where('report_id', $report->id)
            ->where('telegram_member_id', $member->id)
            ->where('status', 'assigned')
            ->first();

        if ($assignment) {
            $assignment->acknowledge();

            $message = "✅ *ĐÃ XÁC NHẬN NHẬN VIỆC*\n\n" .
                "👤 {$member->full_name} đã nhận nhiệm vụ\n" .
                "🎯 Report #{$report->id}\n" .
                "⏰ Bắt đầu: " . now()->format('H:i d/m/Y');

            try {
                $this->bot->sendMessage($chatId, $message, 'Markdown');
            } catch (\Exception $e) {
                Log::error("Failed to send acknowledgment: " . $e->getMessage());
            }
        }
    }

    /**
     * Xử lý khi member hoàn thành việc
     */
    public function completeTask(Report $report, TelegramMember $member, int $chatId): void
    {
        $assignment = TaskAssignment::where('report_id', $report->id)
            ->where('telegram_member_id', $member->id)
            ->whereIn('status', ['assigned', 'acknowledged'])
            ->first();

        if ($assignment) {
            $assignment->complete();

            $timeElapsed = $assignment->getTimeElapsed();
            $hours = floor($timeElapsed / 60);
            $minutes = $timeElapsed % 60;
            $timeString = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";

            $message = "🎉 *HOÀN THÀNH XUẤT SẮC*\n\n" .
                "👤 {$member->full_name}\n" .
                "🎯 Report #{$report->id}\n" .
                "⏱ Thời gian: {$timeString}\n" .
                "✅ Đã hoàn thành nhiệm vụ!";

            try {
                $this->bot->sendMessage($chatId, $message, 'Markdown');
            } catch (\Exception $e) {
                Log::error("Failed to send completion message: " . $e->getMessage());
            }

            // Cập nhật report status
            $allCompleted = $report->taskAssignments()
                    ->whereIn('status', ['assigned', 'acknowledged'])
                    ->count() === 0;

            if ($allCompleted) {
                $report->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
            }
        }
    }

    /**
     * Lấy biểu tượng priority
     */
    protected function getPriorityIcon(string $priority): string
    {
        return match($priority) {
            'high' => '🔥',
            'medium' => '⚡',
            default => 'ℹ️'
        };
    }

    /**
     * Lấy thống kê tasks của một member
     */
    public function getMemberTaskStats(TelegramMember $member): array
    {
        $tasks = $member->taskAssignments;

        return [
            'total' => $tasks->count(),
            'assigned' => $tasks->where('status', 'assigned')->count(),
            'acknowledged' => $tasks->where('status', 'acknowledged')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'overdue' => $tasks->filter(fn($t) => $t->isOverdue())->count(),
            'avg_completion_time' => $tasks
                ->where('status', 'completed')
                ->avg(fn($t) => $t->assigned_at->diffInMinutes($t->completed_at))
        ];
    }

    /**
     * Gửi reminder cho tasks quá hạn
     */
    public function sendOverdueReminders(Location $location): int
    {
        $chatId = $location->chat_id;
        $overdueAssignments = TaskAssignment::whereHas('report', function($query) use ($location) {
            $query->where('location_id', $location->id)
                ->where('deadline', '<', now());
        })->whereIn('status', ['assigned', 'acknowledged'])->get();

        $count = 0;

        foreach ($overdueAssignments as $assignment) {
            $member = $assignment->member;
            $report = $assignment->report;

            $message = "⚠️ *NHẮC NHỞ: VIỆC QUÁ HẠN*\n\n" .
                "👤 {$member->mention}\n" .
                "🎯 Report #{$report->id}\n" .
                "⏰ Deadline: " . $report->deadline->format('H:i d/m/Y') . "\n" .
                "📝 {$assignment->task_description}\n\n" .
                "Vui lòng hoàn thành hoặc báo cáo tình trạng!";

            try {
                $this->bot->sendMessage($chatId, $message, 'Markdown');
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to send overdue reminder: " . $e->getMessage());
            }
        }

        return $count;
    }
}