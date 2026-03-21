<?php

namespace App\Services;

use App\Models\HealthAlert;

class HealthAlertService extends BaseService
{
    public function __construct(HealthAlert $model)
    {
        parent::__construct($model);
    }
}
