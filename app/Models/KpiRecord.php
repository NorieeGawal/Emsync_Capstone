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
        'encoded_by',
        'remarks',
    ];

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