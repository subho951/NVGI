<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id',
        'name',
        'serial_id',   // 👈 add this
        // add other fields you insert via create() / update()
    ];
}
