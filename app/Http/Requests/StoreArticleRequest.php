<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:280'],
            'content_html' => ['required', 'string', 'max:200000'],
            'category' => ['required', Rule::in(array_keys(Article::CATEGORIES))],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_pinned' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'cover_image.image' => 'Cover harus berupa gambar.',
            'cover_image.mimes' => 'Cover hanya boleh JPG, JPEG, PNG, atau WEBP.',
            'cover_image.max' => 'Ukuran cover maksimal 4MB.',
        ];
    }
}
