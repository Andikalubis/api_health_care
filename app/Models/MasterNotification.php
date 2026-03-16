<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterNotification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'notification_type',
    ];
}
