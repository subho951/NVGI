<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classes extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'unit_id'
    ];

    public function subjectLinks()
    {
        return $this->hasMany(ClassSubject::class, 'class_id')->where('status', '=', 1)->orderBy('subject_id', 'ASC');
    }
}
