<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamStudentMark extends Model
{
    use SoftDeletes;

    protected $table = 'exam_student_marks';

    protected $fillable = [
        'exam_id',
        'unit_id',
        'branch_id',
        'class_id',
        'session_id',
        'student_id',
        'full_marks',
        'obtain_marks',
        'marks_percentage',
        'status',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id')->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id')->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')->withTrashed();
    }

    public function examClass()
    {
        return $this->belongsTo(Classes::class, 'class_id')->withTrashed();
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id')->withTrashed();
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id')->withTrashed();
    }
}
