<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveTakenHistory extends Model
{
    protected $table = 'employee_leave_taken_histories';

    protected $fillable = [
        'employee_id',
        'employee_no',
        'employee_name',
        'leave_type_id',
        'leave_date',
        'leave_count',
        'remarks',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'leave_date' => 'date',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
