<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserActivity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_email',
        'user_name',
        'user_type',
        'ip_address',
        'activity_type',
        'activity_details',
        'platform_type',
    ];

}
