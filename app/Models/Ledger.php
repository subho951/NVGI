<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ledger extends Model
{
    use SoftDeletes;

    protected $table = 'ledgers';

    protected $fillable = [
        'type',
        'name',
        'status',
        'created_by',
        'updated_by',
    ];
}
