<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_type_id',
        'warning_min',
        'warning_max',
        'danger_min',
        'danger_max',
    ];

    public function healthType()
    {
        return $this->belongsTo(HealthType::class);
    }
}
