<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'health_type_id',
        'result_value',
        'status',
        'notes',
        'check_time',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientData::class, 'patient_id');
    }

    public function healthType()
    {
        return $this->belongsTo(HealthType::class);
    }

    public function healthAlerts()
    {
        return $this->hasMany(HealthAlert::class);
    }
}
