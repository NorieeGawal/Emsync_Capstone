<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, Prunable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function prunable(): Builder
    {
        // Keep archived accounts recoverable for a month before permanent cleanup.
        return static::onlyTrashed()->where('deleted_at', '<=', now()->subMonth());
    }

    public function scopeWithMetricsForPeriods(Builder $query, array $periods): Builder
    {
        $periods = array_values(array_unique($periods));

        if ($periods === []) {
            return $query;
        }

        sort($periods, SORT_STRING);
        // KPI records need exact periods; attendance logs can be eager-loaded once across the date range.
        $start = Carbon::createFromFormat('Y-m', $periods[0])->startOfMonth()->toDateString();
        $end = Carbon::createFromFormat('Y-m', $periods[count($periods) - 1])->endOfMonth()->toDateString();

        return $query->with([
            'kpiRecords' => fn ($records) => $records->whereIn('period', $periods)->with('kpiIndicator'),
            'attendanceLogs' => fn ($logs) => $logs->whereBetween('date', [$start, $end]),
        ]);
    }

    protected function pruning(): void
    {
        if ($this->profile_photo_path) {
            Storage::disk('public')->delete($this->profile_photo_path);
        }
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function kpiRecords(): HasMany
    {
        return $this->hasMany(KpiRecord::class, 'employee_id');
    }

    public function productivityRecordForPeriod(string $period): ?KpiRecord
    {
        return $this->kpiRecords()
            ->where('period', $period)
            ->whereHas('kpiIndicator', fn ($query) => $query->where('name', 'Productivity'))
            ->first();
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'employee_id');
    }

    public function performanceNotes(): HasMany
    {
        return $this->hasMany(PerformanceNote::class, 'employee_id');
    }

    /**
     * Calculate Attendance % for a given period (e.g. "2026-08").
     * Formula: (days present or late) / (total logged working days) * 100
     */
    public function attendancePercentage(string $period): float
    {
        $logs = $this->attendanceLogsForPeriod($period);

        if ($logs->isEmpty()) {
            return 0;
        }

        $presentDays = $logs->whereIn('status', ['present', 'late'])->count();

        return round(($presentDays / $logs->count()) * 100, 2);
    }

    /**
     * Calculate Punctuality % for a given period.
     * Formula: (days present, not late) / (days present or late) * 100
     */
    public function punctualityPercentage(string $period): float
    {
        $logs = $this->attendanceLogsForPeriod($period)
            ->whereIn('status', ['present', 'late']);

        if ($logs->isEmpty()) {
            return 0;
        }

        $onTimeDays = $logs->where('status', 'present')->count();

        return round(($onTimeDays / $logs->count()) * 100, 2);
    }

    /**
     * Get all 6 KPI scores for a given period, including computed Attendance/Punctuality.
     */
    public function kpiScoresForPeriod(string $period): array
    {
        // Reuse eager-loaded records on dashboards; otherwise fetch only the requested period.
        $records = $this->relationLoaded('kpiRecords')
            ? $this->getRelation('kpiRecords')->where('period', $period)
            : $this->kpiRecords()->where('period', $period)->with('kpiIndicator')->get();
        $records->loadMissing('kpiIndicator');
        $manualScores = $records->pluck('score', 'kpiIndicator.name')->toArray();

        $manualScores = [
            'Productivity' => $manualScores['Productivity'] ?? 0,
            'Efficiency' => $manualScores['Efficiency'] ?? 0,
            'Work Quality' => $manualScores['Work Quality'] ?? 0,
            'Timeliness' => $manualScores['Timeliness'] ?? 0,
            ...$manualScores,
        ];

        $manualScores['Attendance'] = $this->attendancePercentage($period);
        $manualScores['Punctuality'] = $this->punctualityPercentage($period);

        $preferredOrder = ['Productivity', 'Efficiency', 'Work Quality', 'Timeliness', 'Attendance', 'Punctuality'];
        $orderedScores = [];

        foreach ($preferredOrder as $name) {
            if (array_key_exists($name, $manualScores)) {
                $orderedScores[$name] = $manualScores[$name];
            }
        }

        foreach ($manualScores as $name => $score) {
            if (! array_key_exists($name, $orderedScores)) {
                $orderedScores[$name] = $score;
            }
        }

        return $orderedScores;
    }

    /**
     * Calculate the weighted overall KPI score for a given period.
     */
    public function overallScore(string $period): float
    {
        $scores = $this->kpiScoresForPeriod($period);

        $weights = [
            'Productivity' => 0.15,
            'Efficiency' => 0.10,
            'Work Quality' => 0.25,
            'Timeliness' => 0.20,
            'Attendance' => 0.20,
            'Punctuality' => 0.10,
        ];

        if (empty($scores)) {
            return 0;
        }

        $weightedScore = 0;

        foreach ($weights as $name => $weight) {
            $weightedScore += ($scores[$name] ?? 0) * $weight;
        }

        return round($weightedScore, 2);
    }

    private function attendanceLogsForPeriod(string $period)
    {
        $month = Carbon::createFromFormat('Y-m', $period);
        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        if ($this->relationLoaded('attendanceLogs')) {
            return $this->getRelation('attendanceLogs')
                ->filter(fn (AttendanceLog $log): bool => $log->date >= $start
                    && $log->date <= $end
                    && $log->hasAnyRecordedTime());
        }

        return $this->attendanceLogs()
            ->whereBetween('date', [$start, $end])
            ->get()
            ->filter(fn (AttendanceLog $log): bool => $log->hasAnyRecordedTime());
    }
}