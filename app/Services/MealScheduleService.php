<?php

namespace App\Services;

use App\Models\MealSchedule;

class MealScheduleService extends BaseService
{
    public function __construct(MealSchedule $model)
    {
        parent::__construct($model);
    }
}
