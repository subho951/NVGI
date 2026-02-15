<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resume' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'txt'])
                    ->max(5 * 1024), // 5MB
            ],
            'job_description' => 'sometimes|string|max:5000',
        ];
    }
}
