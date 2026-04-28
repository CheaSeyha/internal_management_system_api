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
            // Month (AUG, JAN, etc.)
            'month' => [
                'required',
                'string',
                'in:JAN,FEB,MAR,APR,MAY,JUN,JUL,AUG,SEP,OCT,NOV,DEC',
            ],

            // Year
            'year' => [
                'required',
                'integer',
                'digits:4',
                'min:2000',
                'max:2100',
            ],

            // Staff
            'staff_id' => [
                'required',
                'exists:staff,staff_id',
            ],

            // Rosters array
            'rosters' => [
                'required',
                'array',
                'min:1',
            ],

            // Each roster item
            'rosters.*.work_date' => [
                'required',
                'date',
            ],

            'rosters.*.shift_id' => [
                'required',
                'integer',
                Rule::exists('shifts', 'id'),
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
