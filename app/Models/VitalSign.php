<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VitalSign extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'blood_pressure',
        'heart_rate',
        'body_temperature',
        'breathing_rate',
        'oxygen_level',
        'check_time',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientData::class, 'patient_id');
    }
}
