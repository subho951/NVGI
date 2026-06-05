<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeScheduleRoster extends Model
{
    protected $table = 'employee_schedule_rosters';

    protected $fillable = [
        'employee_id',
        'employee_no',
        'employee_name',
        'category',
        'unit_id',
        'unit_name',
        'branch_id',
        'branch_name',
        'roster_date',
        'roster_month',
        'roster_year',
        'day_name',
        'in_time',
        'out_time',
        'status',
        'generated_at',
        'created_by',
        'updated_by',
    ];
}
