<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamFullMark extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_id',
        'unit_id',
        'class_id',
        'full_marks',
        'status',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id')->withTrashed();
    }

    public function examClass()
    {
        return $this->belongsTo(Classes::class, 'class_id')->withTrashed();
    }
}
