<?php

namespace App\Services;

use App\Models\Medicine;

class MedicineService extends BaseService
{
    public function __construct(Medicine $model)
    {
        parent::__construct($model);
    }
}
