<?php

namespace App\Http\Requests\Flag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFlagRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'key' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9-_]+$/',
                Rule::unique('flags')->where(function ($query) {
                    return $query->where('tenant_id', $this->user()->tenant_id);
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_enabled' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'key.regex' => 'The key must contain only lowercase letters, numbers, hyphens, and underscores.',
            'key.unique' => 'A flag with this key already exists.',
        ];
    }
}
