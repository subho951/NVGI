<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function classLinks()
    {
        return $this->hasMany(ClassSubject::class, 'subject_id')->where('status', '=', 1);
    }
}
