<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Movie::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'release_year' => ['required', 'integer', 'min:1888', 'max:'.(date('Y') + 1)],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'director' => ['nullable', 'string', 'max:255'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'poster' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название фильма обязательно.',
            'release_year.max' => 'Год выпуска указан некорректно.',
        ];
    }
}
