<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterVoterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'year_level' => ['required', 'integer', 'between:1,4'],
            'track' => [
                'nullable',
                Rule::requiredIf(in_array((int) $this->input('year_level'), [3, 4], true)),
                Rule::prohibitedIf(in_array((int) $this->input('year_level'), [1, 2], true)),
                Rule::in(['BA', 'NT']),
            ],
            'semester' => ['required', 'integer', 'between:1,2'],
            'block_id' => [
                'required',
                'integer',
                Rule::exists('blocks', 'id')->where(function (Builder $query): void {
                    $query->where('year_level', $this->input('year_level'))
                        ->where('semester', $this->input('semester'));

                    if ($this->input('track')) {
                        $query->where('track', $this->input('track'));
                    } else {
                        $query->whereNull('track');
                    }
                }),
            ],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }
}
