<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'medicine_id',
        'dosage',
        'drink_time',
        'start_date',
        'end_date',
        'notes',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientData::class, 'patient_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function medicineHistories()
    {
        return $this->hasMany(MedicineHistory::class, 'schedule_id');
    }
}
