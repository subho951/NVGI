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
        'category_salaries',
        'branch',
        'gender',
        'category',
        'in_time',
        'out_time',
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

    public function employeeSalaries()
    {
        return $this->hasMany(EmployeeSalary::class, 'employee_id');
    }
}
