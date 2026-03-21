<?php

namespace App\Services;

use App\Models\MasterNotification;

class MasterNotificationService extends BaseService
{
    public function __construct(MasterNotification $model)
    {
        parent::__construct($model);
    }
}
