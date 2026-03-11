<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'meal_type_id',
        'meal_time',
        'notes',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientData::class, 'patient_id');
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class);
    }
}
