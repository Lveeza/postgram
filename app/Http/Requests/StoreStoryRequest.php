<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoryRequest extends FormRequest
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
        $rules = [
            'type' => ['required', 'string', Rule::in(['image', 'video', 'text'])],
            'background_color' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
                Rule::requiredIf(fn() => $this->type === 'text'),
                Rule::prohibitedIf(fn() => $this->type !== 'text'),
            ],
        ];
        if ($this->type === 'text') {
            $rules['content'] = ['required', 'string'];
        } else {
            $rules['content'] = ['required', 'array'];
            $rules['content.*'] = ['file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi', 'max:50000'];
        }

        return $rules;
    }
}
