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
        'status',
        'deleted_at',
        'created_by',
        'updated_by',
    ];
}
