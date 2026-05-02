<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RosterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'month' => [
                'required',
                'integer',
                'in:1,2,3,4,5,6,7,8,9,10,11,12',
            ],

            'year' => [
                'required',
                'integer',
                'digits:4',
                'min:2000',
                'max:2100',
            ],

            'staff_roster' => [
                'required',
                'array',
                'min:1',
            ],

            'staff_roster.*.staff_id' => [
                'required',
                'exists:staff,staff_id',
            ],

            'staff_roster.*.roster' => [
                'required',
                'array',
                'min:1',
            ],

            'staff_roster.*.roster.*.date' => [
                'required',
                'date_format:Y-m-d',
            ],

            'staff_roster.*.roster.*.shift_name' => [
                'required',
                'string',
                Rule::exists('shifts', 'name'),
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
