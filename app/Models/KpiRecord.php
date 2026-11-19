<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'kpi_indicator_id',
        'period',
        'score',
        'workload_target',
        'workload_completed',
        'encoded_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'workload_target' => 'integer',
            'workload_completed' => 'integer',
        ];
    }

    public static function productivityScore(int $target, int $completed): float
    {
        return round(min(($completed / $target) * 100, 100), 2);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function kpiIndicator(): BelongsTo
    {
        return $this->belongsTo(KpiIndicator::class);
    }

    public function encodedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}