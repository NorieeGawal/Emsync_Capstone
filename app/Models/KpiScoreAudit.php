<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiScoreAudit extends Model
{
    protected $fillable = [
        'transaction_id',
        'employee_id',
        'employee_name',
        'period',
        'kpi_indicator_id',
        'kpi_indicator_name',
        'action',
        'original_value',
        'new_value',
        'actor_id',
        'actor_name',
        'reason',
        'reverses_audit_id',
        'reversed_at',
        'reversed_by',
    ];

    protected function casts(): array
    {
        return [
            'original_value' => 'array',
            'new_value' => 'array',
            'reversed_at' => 'datetime',
        ];
    }
}
