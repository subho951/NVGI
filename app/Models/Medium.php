<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medium extends Model
{
    use SoftDeletes;
    protected $table = 'mediums';
    protected $fillable = [
        'name'
    ];
}
