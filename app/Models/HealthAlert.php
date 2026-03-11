<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_check_id',
        'alert_level',
        'message',
        'sent_status',
    ];

    public function healthCheck()
    {
        return $this->belongsTo(HealthCheck::class);
    }
}
