<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function kpiRecords(): HasMany
    {
        return $this->hasMany(KpiRecord::class, 'employee_id');
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
        $month = Carbon::createFromFormat('Y-m', $period);
        $logs = $this->attendanceLogs()
            ->whereBetween('date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->get();

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
        $month = Carbon::createFromFormat('Y-m', $period);
        $logs = $this->attendanceLogs()
            ->whereBetween('date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->whereIn('status', ['present', 'late'])
            ->get();

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
        $manualScores = $this->kpiRecords()
            ->where('period', $period)
            ->with('kpiIndicator')
            ->get()
            ->pluck('score', 'kpiIndicator.name')
            ->toArray();

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
}