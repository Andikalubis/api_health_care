<?php

namespace App\Services;

use App\Models\HealthType;

class HealthTypeService extends BaseService
{
    public function __construct(HealthType $model)
    {
        parent::__construct($model);
    }
}
