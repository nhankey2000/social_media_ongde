<?php

namespace App\Services;

use App\Models\Location;
use App\Models\TelegramMember;
use TelegramBot\Api\BotApi;
use Illuminate\Support\Facades\Log;

class TelegramMemberService
{
    protected BotApi $bot;

    public function __construct()
    {
        $token = env('TELEGRAM_BOT_TOKEN', '7617448862:AAH7G_WdSzFugy0xqouoxEl1s9xOLy4gwy0');
        $this->bot = new BotApi($token);
    }

    /**
     * Quét tất cả members trong group và lưu vào database
     */
    public function syncGroupMembers(Location $location): array
    {
        try {
            Log::info("Syncing members for location: {$location->name}");

            $chatId = $location->chat_id;
            $members = [];
            $newCount = 0;
            $updatedCount = 0;

            // Lấy danh sách administrators
            $chatAdmins = $this->bot->getChatAdministrators($chatId);

            foreach ($chatAdmins as $admin) {
                $user = $admin->getUser();

                $telegramId = $user->getId();
                $username = $user->getUsername();
                $firstName = $user->getFirstName();
                $lastName = $user->getLastName();
                $fullName = trim($firstName . ' ' . $lastName);

                // Bỏ qua nếu là bot (check bằng username)
                if ($username && (
                        str_ends_with(strtolower($username), 'bot') ||
                        str_contains(strtolower($username), '_bot')
                    )) {
                    Log::info("Skipping bot: {$username}");
                    continue;
                }

                // Tự động phát hiện vai trò
                $role = TelegramMember::detectRole($fullName);

                // Tạo keywords dựa trên vai trò
                $keywords = $role ? TelegramMember::generateKeywords($role) : [];

                // Tìm hoặc tạo member
                $member = TelegramMember::updateOrCreate(
                    [
                        'location_id' => $location->id,
                        'telegram_id' => $telegramId
                    ],
                    [
                        'username' => $username,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'full_name' => $fullName,
                        'role' => $role,
                        'keywords' => $keywords,
                        'is_active' => true,
                        'last_seen_at' => now()
                    ]
                );

                if ($member->wasRecentlyCreated) {
                    $newCount++;
                } else {
                    $updatedCount++;
                }

                $members[] = [
                    'name' => $fullName,
                    'username' => $username,
                    'role' => $role ?? 'Chưa xác định',
                    'status' => $member->wasRecentlyCreated ? 'new' : 'updated'
                ];

                Log::info("Processed member: {$fullName} ({$role})");
            }

            Log::info("Member sync completed", [
                'location' => $location->name,
                'new' => $newCount,
                'updated' => $updatedCount,
                'total' => count($members)
            ]);

            return [
                'success' => true,
                'members' => $members,
                'stats' => [
                    'new' => $newCount,
                    'updated' => $updatedCount,
                    'total' => count($members)
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Failed to sync members: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cập nhật member khi họ gửi tin nhắn
     */
    public function updateMemberFromMessage(Location $location, array $from): TelegramMember
    {
        $telegramId = $from['id'];
        $username = $from['username'] ?? null;
        $firstName = $from['first_name'] ?? '';
        $lastName = $from['last_name'] ?? '';
        $fullName = trim($firstName . ' ' . $lastName);

        $role = TelegramMember::detectRole($fullName);
        $keywords = $role ? TelegramMember::generateKeywords($role) : [];

        return TelegramMember::updateOrCreate(
            [
                'location_id' => $location->id,
                'telegram_id' => $telegramId
            ],
            [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $fullName,
                'role' => $role,
                'keywords' => $keywords,
                'is_active' => true,
                'last_seen_at' => now()
            ]
        );
    }

    /**
     * Tìm members phù hợp với báo cáo
     */
    public function findRelevantMembers(Location $location, string $reportContent, int $limit = 3): array
    {
        $members = TelegramMember::where('location_id', $location->id)
            ->where('is_active', true)
            ->get();

        $scored = [];

        foreach ($members as $member) {
            $score = $member->matchesReport($reportContent);

            if ($score > 0) {
                $scored[] = [
                    'member' => $member,
                    'score' => $score,
                    'role' => $member->role ?? 'Không xác định'
                ];
            }
        }

        // Sắp xếp theo điểm số
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    /**
     * Lấy thống kê members
     */
    public function getMemberStats(Location $location): array
    {
        $members = TelegramMember::where('location_id', $location->id)->get();

        $roleStats = [];
        foreach ($members as $member) {
            $role = $member->role ?? 'Không xác định';
            $roleStats[$role] = ($roleStats[$role] ?? 0) + 1;
        }

        return [
            'total' => $members->count(),
            'active' => $members->where('is_active', true)->count(),
            'roles' => $roleStats,
            'last_sync' => $members->max('updated_at')
        ];
    }

    /**
     * Gửi tin nhắn mention một member cụ thể
     */
    public function mentionMember(int $chatId, TelegramMember $member, string $message): bool
    {
        try {
            $mention = $member->mention;
            $fullMessage = "{$mention}\n\n{$message}";

            $this->bot->sendMessage($chatId, $fullMessage, 'Markdown');

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to mention member: " . $e->getMessage());
            return false;
        }
    }
}