<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBlockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isElectionManager() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'program' => ['required', 'string', 'max:100'],
            'section' => ['required', 'string', 'max:20'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'semester' => ['required', 'integer', 'between:1,3'],
            'track' => ['nullable', 'string', 'max:10'],
        ];
    }
}
