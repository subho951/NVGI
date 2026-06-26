<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryGeneration extends Model
{
    protected $table = 'salary_generations';

    protected $fillable = [
        'salary_month',
        'salary_year',
        'branch_name',
        'employee_id',
        'employee_no',
        'employee_name',
        'employee_category',
        'doj',
        'gross_salary',
        'earning_total',
        'deduction_total',
        'net_salary',
        'absent_days',
        'unpaid_absent_days',
        'absent_amount',
        'assigned_hours',
        'attendance_hours',
        'late_count',
        'late_penalty_units',
        'cl_alloted',
        'cl_balance',
        'ml_alloted',
        'ml_balance',
        'leave_details',
        'salary_head_details',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'salary_month' => 'integer',
        'salary_year' => 'integer',
        'gross_salary' => 'decimal:2',
        'earning_total' => 'decimal:2',
        'deduction_total' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'absent_days' => 'decimal:2',
        'unpaid_absent_days' => 'decimal:2',
        'absent_amount' => 'decimal:2',
        'assigned_hours' => 'decimal:2',
        'attendance_hours' => 'decimal:2',
        'late_count' => 'integer',
        'late_penalty_units' => 'decimal:2',
        'cl_alloted' => 'decimal:2',
        'cl_balance' => 'decimal:2',
        'ml_alloted' => 'decimal:2',
        'ml_balance' => 'decimal:2',
    ];
}
