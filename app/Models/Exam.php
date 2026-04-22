<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function fullMarks()
    {
        return $this->hasMany(ExamFullMark::class, 'exam_id')->orderBy('unit_id', 'ASC')->orderBy('class_id', 'ASC');
    }
}
