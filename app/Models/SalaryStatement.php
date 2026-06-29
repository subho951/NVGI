<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStatement extends Model
{
    protected $table = 'salary_statements';

    protected $fillable = [
        'salary_generation_id',
        'salary_month',
        'salary_year',
        'branch_name',
        'employee_id',
        'employee_no',
        'employee_name',
        'employee_category',
        'salary_amount',
        'bank_name',
        'bank_branch',
        'account_no',
        'ifsc_code',
        'account_type',
        'status',
        'generated_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'salary_month' => 'integer',
        'salary_year' => 'integer',
        'salary_amount' => 'decimal:2',
    ];

    public function salaryGeneration()
    {
        return $this->belongsTo(SalaryGeneration::class, 'salary_generation_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
