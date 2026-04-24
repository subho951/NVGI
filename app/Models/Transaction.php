<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'bank_account_id',
        'payment_reference',
        'type',
        'ledger_id',
        'transaction_timestamp',
        'transaction_amount',
        'particulars',
        'note',
        'status',
        'created_by',
        'updated_by',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
