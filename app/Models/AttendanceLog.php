<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'status',
    ];

    public function hasAnyRecordedTime(): bool
    {
        return filled($this->time_in) || filled($this->time_out);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}