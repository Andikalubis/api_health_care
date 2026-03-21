<?php

namespace App\Services;

use App\Models\MedicineSchedule;

class MedicineScheduleService extends BaseService
{
    public function __construct(MedicineSchedule $model)
    {
        parent::__construct($model);
    }
}
