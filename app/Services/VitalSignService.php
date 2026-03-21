<?php

namespace App\Services;

use App\Models\VitalSign;

class VitalSignService extends BaseService
{
    public function __construct(VitalSign $model)
    {
        parent::__construct($model);
    }
}
