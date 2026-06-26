<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveAllotment extends Model
{
    protected $table = 'employee_leave_allotments';

    protected $fillable = [
        'leave_allotment_id',
        'employee_id',
        'employee_no',
        'employee_name',
        'employee_category',
        'leave_type_id',
        'leave_tenure_from',
        'leave_tenure_to',
        'current_allotment',
        'previous_balance',
        'total_allotment',
        'used_leave',
        'balance_leave',
        'status',
        'assigned_by',
    ];

    protected $casts = [
        'leave_tenure_from' => 'date',
        'leave_tenure_to' => 'date',
    ];

    public function leaveAllotment()
    {
        return $this->belongsTo(LeaveAllotment::class, 'leave_allotment_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
