<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'bank_name',
        'bank_branch',
        'account_no',
        'ifsc_code',
        'account_type',
        'status',
    ];
}
