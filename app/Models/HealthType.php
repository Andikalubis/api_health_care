<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'normal_min',
        'normal_max',
        'description',
    ];

    public function healthChecks()
    {
        return $this->hasMany(HealthCheck::class);
    }

    public function healthLimits()
    {
        return $this->hasMany(HealthLimit::class);
    }
}
