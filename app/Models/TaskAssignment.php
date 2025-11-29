<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAssignment extends Model
{
    protected $fillable = [
        'report_id',
        'telegram_member_id',
        'task_description',
        'assigned_at',
        'acknowledged_at',
        'completed_at',
        'status'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // === RELATIONSHIPS ===

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(TelegramMember::class, 'telegram_member_id');
    }

    // === METHODS ===

    /**
     * Đánh dấu đã xác nhận nhận việc
     */
    public function acknowledge(): void
    {
        $this->update([
            'acknowledged_at' => now(),
            'status' => 'acknowledged'
        ]);
    }

    /**
     * Đánh dấu hoàn thành
     */
    public function complete(): void
    {
        $this->update([
            'completed_at' => now(),
            'status' => 'completed'
        ]);
    }

    /**
     * Kiểm tra xem có quá hạn không
     */
    public function isOverdue(): bool
    {
        if (!$this->report->deadline) {
            return false;
        }

        return now()->isAfter($this->report->deadline)
            && !in_array($this->status, ['completed']);
    }

    /**
     * Tính thời gian đã giao
     */
    public function getTimeElapsed(): int
    {
        return $this->assigned_at->diffInMinutes(now());
    }
}