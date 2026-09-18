<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'body'  => 'required|string|min:10',

            'media' => 'nullable|array',
            'media.*.type' => 'required|string|in:image,video,text',
            'media.*.content' => 'required',
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
