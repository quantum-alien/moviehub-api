<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'year_from' => ['nullable', 'integer', 'min:1888'],
            'year_to' => ['nullable', 'integer', 'min:1888'],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'sort_by' => ['nullable', 'string', 'in:created_at,release_year,avg_rating,title'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
