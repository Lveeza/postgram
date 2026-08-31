<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|min:3',
            'body'  => 'required|min:10',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // 2MB

        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'title.min'      => 'The title must be at least 3 characters long.',
            'body.required'  => 'The body field is required.',
            'body.min'       => 'The body must be at least 10 characters long.',
        ];
    }
}
