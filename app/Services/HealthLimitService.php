<?php

namespace App\Services;

use App\Models\HealthLimit;

class HealthLimitService extends BaseService
{
    public function __construct(HealthLimit $model)
    {
        parent::__construct($model);
    }
}
