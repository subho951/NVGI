<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSubject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id',
        'class_id',
        'subject_id',
        'status',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id')->withTrashed();
    }

    public function examClass()
    {
        return $this->belongsTo(Classes::class, 'class_id')->withTrashed();
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id')->withTrashed();
    }
}
