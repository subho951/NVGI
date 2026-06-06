<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    protected $table = 'employee_attendances';

    protected $fillable = [
        'roster_id',
        'employee_id',
        'employee_no',
        'employee_name',
        'category',
        'unit_id',
        'branch_id',
        'branch_name',
        'attendance_date',
        'scheduled_in_time',
        'scheduled_out_time',
        'is_late',
        'late_minutes',
        'punch_in_at',
        'punch_in_image',
        'punch_in_ip',
        'punch_in_portal_branch_id',
        'punch_out_at',
        'punch_out_image',
        'punch_out_ip',
        'punch_out_portal_branch_id',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
        'punch_in_at' => 'datetime',
        'punch_out_at' => 'datetime',
    ];
}
