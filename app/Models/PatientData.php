<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientData extends Model
{
    use HasFactory;

    protected $table = 'patient_data';

    protected $fillable = [
        'user_id',
        'name',
        'gender',
        'birth_date',
        'height',
        'weight',
        'blood_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function healthChecks()
    {
        return $this->hasMany(HealthCheck::class, 'patient_id');
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class, 'patient_id');
    }

    public function medicineSchedules()
    {
        return $this->hasMany(MedicineSchedule::class, 'patient_id');
    }

    public function mealSchedules()
    {
        return $this->hasMany(MealSchedule::class, 'patient_id');
    }
}
