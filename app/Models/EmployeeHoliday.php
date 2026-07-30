<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHoliday extends Model
{
    protected $table = 'employee_holidays';

    protected $fillable = [
        'holiday_date',
        'name',
        'branch_name',
        'category',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'holiday_date' => 'date',
    ];
}
