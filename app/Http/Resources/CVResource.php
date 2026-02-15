<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CVResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'personal_information' => [
                'name' => $this->resource['personal_information']['name'] ?? null,
                'email' => $this->resource['personal_information']['email'] ?? null,
                'phone' => $this->resource['personal_information']['phone'] ?? null,
                'location' => $this->resource['personal_information']['location'] ?? null,
                'address' => $this->resource['personal_information']['address'] ?? null,
                'date_of_birth' => $this->resource['personal_information']['date_of_birth'] ?? null,
                'nationality' => $this->resource['personal_information']['nationality'] ?? null,
                'linkedin' => $this->resource['personal_information']['linkedin'] ?? null,
                'website' => $this->resource['personal_information']['website'] ?? null,
            ],
            'summary' => $this->resource['summary'] ?? null,
            'education' => $this->resource['education'] ?? [],
            'experience' => $this->resource['experience'] ?? [],
            'skills' => $this->resource['skills'] ?? [],
            'languages' => $this->resource['languages'] ?? [],
            'certifications' => $this->resource['certifications'] ?? [],
            'projects' => $this->resource['projects'] ?? [],
            'publications' => $this->resource['publications'] ?? [],
            'awards' => $this->resource['awards'] ?? [],
            'references' => $this->resource['references'] ?? [],
            'social_profiles' => $this->resource['social_profiles'] ?? [],
            'interests' => $this->resource['interests'] ?? [],
            'metadata' => [
                'confidence_score' => $this->resource['metadata']['confidence_score'] ?? null,
                'parsed_at' => $this->resource['metadata']['parsed_at'] ?? now()->toIso8601String(),
                'file_type' => $this->when(isset($this->resource['metadata']['file_type']),
                    function () {
                        return $this->resource['metadata']['file_type'];
                    }
                ),
            ],
        ];
    }
}
