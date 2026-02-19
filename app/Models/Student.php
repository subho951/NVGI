<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sl_no',
        'student_id_serial',
        'unit_id',
        'branch_id',
        'session_id',
        'admission_date',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'gender',
        'religion_id',
        'caste',
        'dob',
        'is_ph',
        'permanent_address',
        'permanent_pincode',
        'vhs_class_id',
        'vhs_daycare',
        'tsa_class_id',
        'tsa_board',
        'tsa_subjects',
        'tsa_medium',
        'father_name',
        'father_occupation',
        'father_mobile',
        'mother_name',
        'mother_occupation',
        'mother_mobile',
        'emergency_name',
        'emergency_phone',
        'emergency_relation',
        'know_about_us',
        'photo',
        'created_by',
        'updated_by',
    ];
}
