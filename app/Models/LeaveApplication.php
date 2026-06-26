<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $table = 'leave_applications';

    protected $fillable = [
        'employee_id',
        'employee_no',
        'employee_name',
        'employee_category',
        'leave_type_id',
        'leave_type_name',
        'leave_from_date',
        'leave_to_date',
        'no_of_days',
        'apply_date',
        'remarks',
        'application_status',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'reject_reason',
        'leave_taken_history_id',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'leave_from_date' => 'date',
        'leave_to_date' => 'date',
        'apply_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
