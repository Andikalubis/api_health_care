<?php

namespace App\Services;

use App\Models\PatientData;

class PatientDataService extends BaseService
{
    public function __construct(PatientData $model)
    {
        parent::__construct($model);
    }
}
