<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterEmployeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_name'          => ['nullable', 'string', 'max:255'],
            'department'          => ['nullable', 'array'],
            'department.*'        => ['string'],
            'position'            => ['nullable', 'array'],
            'position.*'          => ['string'],
            'employment_status'   => ['nullable', 'array'],
            'employment_status.*' => ['string', Rule::in(['working', 'resigned', 'terminated', 'probation'])],
        ];
    }
}
