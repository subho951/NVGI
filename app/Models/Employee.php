<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'sl_no',
        'employee_no',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'address',
        'pincode',
        'dob',
        'age',
        'doj',
        'image',
        'salary',
        'branch',
        'gender',
        'aadhar_no',
        'bank_name',
        'bank_branch',
        'account_no',
        'ifsc_code',
        'account_type',
        'status',
        'deleted_at',
        'created_by',
        'updated_by',
    ];
}
