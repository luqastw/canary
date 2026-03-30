<?php

namespace App\Http\Requests\Evaluation;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateRequest extends FormRequest
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
            'flag_key' => ['required', 'string', 'max:100'],
            'context' => ['required', 'array'],
            'context.user_id' => ['required', 'string', 'max:255'],
            'context.role' => ['required', 'string', 'max:100'],
            'context.metadata' => ['nullable', 'array'],
        ];
    }
}
