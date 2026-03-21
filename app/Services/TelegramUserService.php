<?php

namespace App\Services;

use App\Models\TelegramUser;

class TelegramUserService extends BaseService
{
    public function __construct(TelegramUser $model)
    {
        parent::__construct($model);
    }
}
