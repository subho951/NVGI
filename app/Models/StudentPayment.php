<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentPayment extends Model
{
    use SoftDeletes;
    protected $table = 'student_payments';
    protected $fillable = [
        'student_id',
        'unit_id',
        'branch_id',
        'payable_month',
        'payable_year',
        'payable_amount',
        'payment_amount',
        'payment_date',
        'due_amount',
        'created_by',
        'updated_by',
    ];
}
