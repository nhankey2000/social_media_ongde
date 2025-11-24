<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_id',
        'reporter_name',
        'reporter_telegram_id',
        'reporter_username',
        'content',
        'ai_response',
        'status',
        'priority',
        'deadline',
        'completed_at',
        'completed_by',
        'metadata',
        'processing_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'reporter_telegram_id' => 'integer',
        'processing_time' => 'integer',
    ];

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_OVERDUE = 'overdue';

    /**
     * Priority constants.
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    /**
     * Get the location that owns the report.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by priority.
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Overdue reports.
     */
    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Scope: Today's reports.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: This week's reports.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope: This month's reports.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Scope: High priority reports.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', self::PRIORITY_HIGH);
    }

    /**
     * Mark report as completed.
     */
    public function markAsCompleted(string $completedBy = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => $completedBy,
            'processing_time' => $this->calculateProcessingTime(),
        ]);
    }

    /**
     * Mark report as overdue.
     */
    public function markAsOverdue(): void
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    /**
     * Calculate processing time in minutes.
     */
    public function calculateProcessingTime(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->completed_at);
    }

    /**
     * Check if report is overdue.
     */
    public function isOverdue(): bool
    {
        if (!$this->deadline) {
            return false;
        }

        return $this->deadline < now() && 
               !in_array($this->status, [self::STATUS_COMPLETED]);
    }

    /**
     * Check if report is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get time remaining until deadline.
     */
    public function getTimeRemaining(): ?string
    {
        if (!$this->deadline) {
            return null;
        }

        if ($this->isOverdue()) {
            $diff = now()->diffInMinutes($this->deadline);
            return "Quá hạn {$diff} phút";
        }

        return $this->deadline->diffForHumans();
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'gray',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_OVERDUE => 'red',
            default => 'gray'
        };
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status label in Vietnamese.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Đang chờ',
            self::STATUS_IN_PROGRESS => 'Đang xử lý',
            self::STATUS_COMPLETED => 'Đã hoàn thành',
            self::STATUS_OVERDUE => 'Quá hạn',
            default => 'Không xác định'
        };
    }

    /**
     * Get priority label in Vietnamese.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Thấp',
            self::PRIORITY_MEDIUM => 'Trung bình',
            self::PRIORITY_HIGH => 'Cao',
            default => 'Không xác định'
        };
    }

    /**
     * Auto-check for overdue reports (can be called by scheduler).
     */
    public static function checkOverdueReports(): int
    {
        $overdueReports = self::overdue()->get();
        
        foreach ($overdueReports as $report) {
            $report->markAsOverdue();
        }

        return $overdueReports->count();
    }

    /**
     * Get short content for display.
     */
    public function getShortContentAttribute(): string
    {
        return str($this->content)->limit(100);
    }

    /**
     * Get short AI response for display.
     */
    public function getShortAiResponseAttribute(): string
    {
        return str($this->ai_response)->limit(150);
    }
}
