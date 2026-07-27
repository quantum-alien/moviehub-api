<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('movie')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'release_year' => ['sometimes', 'required', 'integer', 'min:1888', 'max:'.(date('Y') + 1)],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'director' => ['nullable', 'string', 'max:255'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => [Rule::exists('genres', 'id')],
            'poster' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
