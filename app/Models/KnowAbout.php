<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowAbout extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];
}
