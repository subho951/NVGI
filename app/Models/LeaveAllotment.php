<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveAllotment extends Model
{
    use SoftDeletes;

    protected $table = 'leave_allotments';

    protected $fillable = [
        'leave_tenure_from',
        'leave_tenure_to',
        'leave_type_id',
        'front_desk_leave_count',
        'group_d_leave_count',
        'tsa_teacher_leave_count',
        'vhs_teacher_leave_count',
        'is_carry_forward',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'leave_tenure_from' => 'date',
        'leave_tenure_to' => 'date',
        'is_carry_forward' => 'boolean',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function employeeAllotments()
    {
        return $this->hasMany(EmployeeLeaveAllotment::class, 'leave_allotment_id');
    }
}
