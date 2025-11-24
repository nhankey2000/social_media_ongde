<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'chat_id',
        'manager_name',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'chat_id' => 'integer',
    ];

    /**
     * Get all reports for this location.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Get pending reports.
     */
    public function pendingReports(): HasMany
    {
        return $this->reports()->where('status', 'pending');
    }

    /**
     * Get in-progress reports.
     */
    public function inProgressReports(): HasMany
    {
        return $this->reports()->where('status', 'in_progress');
    }

    /**
     * Get overdue reports.
     */
    public function overdueReports(): HasMany
    {
        return $this->reports()->where('status', 'overdue');
    }

    /**
     * Get completed reports.
     */
    public function completedReports(): HasMany
    {
        return $this->reports()->where('status', 'completed');
    }

    /**
     * Get today's reports.
     */
    public function todayReports(): HasMany
    {
        return $this->reports()->whereDate('created_at', today());
    }

    /**
     * Scope: Only active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Has telegram integration.
     */
    public function scopeHasTelegram($query)
    {
        return $query->whereNotNull('chat_id');
    }

    /**
     * Get location statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_reports' => $this->reports()->count(),
            'pending' => $this->pendingReports()->count(),
            'in_progress' => $this->inProgressReports()->count(),
            'overdue' => $this->overdueReports()->count(),
            'completed' => $this->completedReports()->count(),
            'today' => $this->todayReports()->count(),
            'completion_rate' => $this->getCompletionRate(),
            'average_processing_time' => $this->getAverageProcessingTime(),
        ];
    }

    /**
     * Calculate completion rate (%).
     */
    public function getCompletionRate(): float
    {
        $total = $this->reports()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->completedReports()->count();
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Calculate average processing time (minutes).
     */
    public function getAverageProcessingTime(): ?float
    {
        return $this->reports()
            ->whereNotNull('processing_time')
            ->avg('processing_time');
    }

    /**
     * Check if location has overdue reports.
     */
    public function hasOverdueReports(): bool
    {
        return $this->overdueReports()->exists();
    }

    /**
     * Get display name with code.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}
