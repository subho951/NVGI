<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $table = 'transactions';
    protected $fillable = [
        'sl_no',
        'txn_no',
        'fee_id',
        'unit_id',
        'branch_id',
        'payment_mode',
        'payment_reference',
        'type',
        'transaction_timestamp',
        'transaction_amount',
        'particulars',
        'note',
        'status',
        'created_by',
        'updated_by',
    ];
}
