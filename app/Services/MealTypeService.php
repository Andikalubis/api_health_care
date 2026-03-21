<?php

namespace App\Services;

use App\Models\MealType;

class MealTypeService extends BaseService
{
    public function __construct(MealType $model)
    {
        parent::__construct($model);
    }
}
