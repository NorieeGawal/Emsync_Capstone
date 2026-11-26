<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'scheduled_time_in',
        'scheduled_time_out',
    ];

    public static function current(): self
    {
        // Create baseline hours on first use so attendance entry always has a schedule to compare against.
        return static::firstOrCreate([], [
            'scheduled_time_in' => '08:00:00',
            'scheduled_time_out' => '17:00:00',
        ]);
    }
}
