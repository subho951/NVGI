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
        'salary_period_start',
        'salary_period_end',
        'eligible_days',
        'gross_salary',
        'payable_gross_salary',
        'earning_total',
        'deduction_total',
        'net_salary',
        'absent_days',
        'unpaid_absent_days',
        'approved_leave_days',
        'holiday_days',
        'pre_doj_excluded_days',
        'absent_amount',
        'assigned_hours',
        'attendance_hours',
        'late_count',
        'late_penalty_units',
        'late_amount',
        'cl_alloted',
        'cl_balance',
        'ml_alloted',
        'ml_balance',
        'leave_details',
        'attendance_details',
        'salary_head_details',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'salary_month' => 'integer',
        'salary_year' => 'integer',
        'doj' => 'date',
        'salary_period_start' => 'date',
        'salary_period_end' => 'date',
        'gross_salary' => 'decimal:2',
        'payable_gross_salary' => 'decimal:2',
        'eligible_days' => 'decimal:2',
        'earning_total' => 'decimal:2',
        'deduction_total' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'absent_days' => 'decimal:2',
        'unpaid_absent_days' => 'decimal:2',
        'approved_leave_days' => 'decimal:2',
        'holiday_days' => 'decimal:2',
        'pre_doj_excluded_days' => 'decimal:2',
        'absent_amount' => 'decimal:2',
        'assigned_hours' => 'decimal:2',
        'attendance_hours' => 'decimal:2',
        'late_count' => 'integer',
        'late_penalty_units' => 'decimal:2',
        'late_amount' => 'decimal:2',
        'cl_alloted' => 'decimal:2',
        'cl_balance' => 'decimal:2',
        'ml_alloted' => 'decimal:2',
        'ml_balance' => 'decimal:2',
    ];
}
