<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id',
        'name',
        'serial_id',
        'password',
        'original_password',
    ];

    protected $hidden = [
        'password',
        'original_password',
    ];

    public function getOriginalPasswordForDisplayAttribute(): string
    {
        if (empty($this->original_password)) {
            return '';
        }

        try {
            return Crypt::decryptString($this->original_password);
        } catch (\Throwable $e) {
            return '';
        }
    }
}
