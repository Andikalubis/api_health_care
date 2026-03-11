<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineHistory extends Model
{
    use HasFactory;

    protected $table = 'medicine_histories';

    protected $fillable = [
        'schedule_id',
        'taken_time',
        'status',
    ];

    public function medicineSchedule()
    {
        return $this->belongsTo(MedicineSchedule::class, 'schedule_id');
    }
}
