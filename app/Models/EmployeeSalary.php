<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    protected $table = 'employee_salaries';

    protected $fillable = [
        'employee_id',
        'employee_no',
        'employee_name',
        'employee_category',
        'gross_salary',
        'salary_head_id',
        'salary_head_name',
        'salary_head_type',
        'calculation_type',
        'calculation_base',
        'calculation_amount',
        'formula_label',
        'is_payslip_show',
        'calculated_amount',
        'status',
        'created_by',
        'updated_by',
        'calculated_by',
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'calculation_amount' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'is_payslip_show' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function salaryHead()
    {
        return $this->belongsTo(SalaryHead::class, 'salary_head_id');
    }
}
