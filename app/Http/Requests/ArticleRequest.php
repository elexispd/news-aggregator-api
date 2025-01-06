<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
            'keyword' => 'string|nullable',
            'date_from' => 'date|nullable',
            'date_to' => 'date|nullable|after_or_equal:date_from',
            'category_id' => 'integer|exists:categories,id|nullable',
            'source_id' => 'integer|exists:sources,id|nullable',
        ];
    }
}
