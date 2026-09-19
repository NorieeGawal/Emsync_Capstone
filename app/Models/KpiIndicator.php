<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiIndicator extends Model
{
    protected $fillable = ['name'];

    public function kpiRecords(): HasMany
    {
        return $this->hasMany(KpiRecord::class);
    }
}