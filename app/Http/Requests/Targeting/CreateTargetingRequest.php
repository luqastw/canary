<?php

namespace App\Http\Requests\Targeting;

use Illuminate\Foundation\Http\FormRequest;

class CreateTargetingRequest extends FormRequest
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
            'flag_id' => ['required', 'integer', 'exists:flags,id'],
            'group_ids' => ['required', 'array', 'min:1'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
        ];
    }
}
