<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Calender extends Model
{

    const table = 'calendars';

    protected $table = self::table;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'event_type',
        'priority',
        'start_date',
        'end_date',
        'location',
        'meeting_link',
        'timezone',
        'color',
        'tags',
        'status',
        'is_private',
        'attachments',
        'company_id',
        'created_by',
        'attendees',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_interval',
        'recurrence_end_date',
        'send_notifications',
        'notification_settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'recurrence_end_date' => 'datetime',
        'is_recurring' => 'boolean',
        'is_private' => 'boolean',
        'send_notifications' => 'boolean',
        'tags' => 'array',
        'attendees' => 'array',
        'attachments' => 'array',
        'notification_settings' => 'array',
    ];

    /**
     * Event status enum
     */
    const STATUS_UPCOMING = 'upcoming';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';
    const STATUS_POSTPONED = 'postponed';

    /**
     * Priority levels
     */
    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW = 'low';

    /**
     * Get the company that owns the event.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attendees as users collection.
     */
    public function getAttendeesAsUsers()
    {
        return User::whereIn('id', $this->attendees ?? [])->get();
    }

    /**
     * Scope for upcoming events.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>=', Carbon::now())
                    ->where('status', self::STATUS_UPCOMING);
    }

    /**
     * Scope for events within a date range.
     */
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Scope for events by company.
     */
    public function scopeByCompany(Builder $query, $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope for events by priority.
     */
    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Check if event is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->end_date && Carbon::now() > $this->end_date;
    }

    /**
     * Check if event is in progress.
     */
    public function isInProgress(): bool
    {
        $now = Carbon::now();
        return $now >= $this->start_date && 
               (!$this->end_date || $now <= $this->end_date);
    }

    /**
     * Generate recurring events.
     */
    public function generateRecurringEvents(): array
    {
        if (!$this->is_recurring || !$this->recurrence_pattern) {
            return [];
        }

        $events = [];
        $currentDate = Carbon::parse($this->start_date);
        $endDate = $this->recurrence_end_date 
            ? Carbon::parse($this->recurrence_end_date)
            : $currentDate->copy()->addMonths(3); // Default 3 months if no end date

        while ($currentDate <= $endDate) {
            // Skip the original event date
            if ($currentDate->ne(Carbon::parse($this->start_date))) {
                $events[] = $this->replicateForDate($currentDate);
            }

            // Increment based on recurrence pattern
            switch ($this->recurrence_pattern) {
                case 'daily':
                    $currentDate->addDays($this->recurrence_interval ?? 1);
                    break;
                case 'weekly':
                    $currentDate->addWeeks($this->recurrence_interval ?? 1);
                    break;
                case 'monthly':
                    $currentDate->addMonths($this->recurrence_interval ?? 1);
                    break;
            }
        }

        return $events;
    }

    /**
     * Replicate event for a new date.
     */
    protected function replicateForDate(Carbon $newDate): self
    {
        $replica = $this->replicate(['id', 'created_at', 'updated_at']);
        
        // Calculate duration of original event
        $duration = $this->end_date 
            ? Carbon::parse($this->start_date)->diffInSeconds($this->end_date)
            : 0;

        $replica->start_date = $newDate;
        if ($this->end_date) {
            $replica->end_date = $newDate->copy()->addSeconds($duration);
        }

        return $replica;
    }
}