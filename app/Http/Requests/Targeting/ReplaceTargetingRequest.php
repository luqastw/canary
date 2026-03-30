<?php

namespace App\Http\Requests\Targeting;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceTargetingRequest extends FormRequest
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
            'group_ids' => ['present', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
        ];
    }
}
