<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function medicineSchedules()
    {
        return $this->hasMany(MedicineSchedule::class);
    }
}
