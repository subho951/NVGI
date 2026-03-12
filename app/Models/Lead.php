<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;
    protected $table = 'leads';
    protected $fillable = [
        'sl_no',
        'lead_no',
        'sales_person_id',
        'student_name',
        'guardian_name',
        'phone',
        'remarks1',
        'remarks2',
        'remarks3',
        'remarks4',
        'remarks5',
        'created_by',
        'updated_by',
    ];
}
