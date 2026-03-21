<?php

namespace App\Services;

use App\Models\MedicineHistory;

class MedicineHistoryService extends BaseService
{
    public function __construct(MedicineHistory $model)
    {
        parent::__construct($model);
    }
}
