<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class TelegramMember extends Model
{
    protected $fillable = [
        'location_id',
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'full_name',
        'role',
        'keywords',
        'is_active',
        'last_seen_at'
    ];

    protected $casts = [
        'keywords' => 'json', // Changed to json for better stability
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime'
    ];

    // Override to ensure keywords is always array
    public function getKeywordsAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    public function setKeywordsAttribute($value)
    {
        if (is_array($value)) {
            // Clean and encode
            $value = array_values(array_filter($value, fn($k) => !empty(trim($k))));
            $this->attributes['keywords'] = json_encode($value);
        } else {
            $this->attributes['keywords'] = json_encode([]);
        }
    }

    // === RELATIONSHIPS ===

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    // === METHODS ===

    /**
     * Tự động phát hiện vai trò từ tên
     */
    public static function detectRole(string $fullName): ?string
    {
        $rolePatterns = [
            'IT' => ['IT', 'Tech', 'Kỹ thuật', 'Lập trình'],
            'Bảo trì' => ['Bảo trì', 'Sửa chữa', 'Kỹ thuật', 'Thợ'],
            'Kế toán' => ['Kế toán', 'Accounting', 'Tài chính'],
            'Phục vụ' => ['Phục vụ', 'Waiter', 'Nhân viên phục vụ'],
            'Bếp' => ['Bếp', 'Chef', 'Cook', 'Đầu bếp'],
            'Lễ tân' => ['Lễ tân', 'Reception', 'Front desk'],
            'Quản lý' => ['Quản lý', 'Manager', 'Trưởng', 'Giám đốc'],
        ];

        $nameLower = mb_strtolower($fullName);

        foreach ($rolePatterns as $role => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($nameLower, mb_strtolower($pattern))) {
                    return $role;
                }
            }
        }

        return null;
    }

    /**
     * Tự động tạo keywords từ vai trò
     */
    public static function generateKeywords(string $role): array
    {
        $keywordMap = [
            'IT' => [
                'máy tính', 'laptop', 'mạng', 'wifi', 'phần mềm', 'hệ thống',
                'internet', 'server', 'database', 'website', 'app', 'pos',
                'in ấn', 'máy in', 'scanner', 'email', 'bảo mật'
            ],
            'Bảo trì' => [
                'hỏng', 'hư', 'sửa', 'thay', 'máy lạnh', 'điện', 'nước',
                'ống nước', 'bóng đèn', 'quạt', 'cửa', 'khóa', 'tường',
                'sơn', 'bàn ghế', 'thiết bị', 'công cụ'
            ],
            'Kế toán' => [
                'tiền', 'doanh thu', 'chi phí', 'hóa đơn', 'thanh toán',
                'ngân sách', 'lương', 'thuế', 'báo cáo tài chính', 'kế toán'
            ],
            'Phục vụ' => [
                'khách', 'phục vụ', 'order', 'gọi món', 'mang đồ',
                'dọn bàn', 'thái độ', 'chất lượng phục vụ'
            ],
            'Bếp' => [
                'món ăn', 'nấu', 'chế biến', 'nguyên liệu', 'thực phẩm',
                'bếp', 'nồi', 'chảo', 'dao', 'vệ sinh bếp'
            ],
            'Lễ tân' => [
                'check in', 'check out', 'đặt bàn', 'reservation',
                'khách hàng', 'điện thoại', 'tư vấn'
            ],
        ];

        return $keywordMap[$role] ?? [];
    }

    /**
     * Kiểm tra xem member có phù hợp với báo cáo không
     */
    public function matchesReport(string $reportContent): int
    {
        if (!$this->keywords || empty($this->keywords)) {
            return 0;
        }

        $contentLower = mb_strtolower($reportContent);
        $matchCount = 0;

        foreach ($this->keywords as $keyword) {
            if (str_contains($contentLower, mb_strtolower($keyword))) {
                $matchCount++;
            }
        }

        return $matchCount;
    }

    /**
     * Lấy danh sách task đang active
     */
    public function getActiveTasks()
    {
        return $this->taskAssignments()
            ->whereIn('status', ['assigned', 'acknowledged'])
            ->with('report')
            ->get();
    }

    /**
     * Format mention cho Telegram
     */
    public function getMentionAttribute(): string
    {
        if ($this->username) {
            return "@{$this->username}";
        }
        return "[{$this->full_name}](tg://user?id={$this->telegram_id})";
    }
}