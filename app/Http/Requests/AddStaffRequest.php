<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddStaffRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'staff_id' => [
                'required',
                'numeric',
                Rule::unique('staff', 'staff_id'),
            ],

            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone_number' => 'required|string',
            'genders' => 'required|string',
            'label_id' => 'required|string',

            'department_name' => [
                'required',
                Rule::exists('departments', 'department_name'),
            ],

            'position_name' => [
                'required',
                Rule::exists('positions', 'position_name'),
            ],

            'role_name' => [
                'required',
                Rule::exists('roles', 'role_name'),
            ],

            'status' => 'required|string',
            'date_of_birth' => 'required|date',

            'email' => 'required|email|max:255|unique:staff,email',
            'password' => 'required|string|min:8',

            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $departmentName = $this->input('department_name');
            $positionName   = $this->input('position_name');

            // Only check if both are provided
            if (!$departmentName || !$positionName) {
                return;
            }

            $department = Department::where('department_name', $departmentName)->first();

            if (!$department) {
                // already handled by rules(), but keep safe
                return;
            }

            $positionExists = Position::where('position_name', $positionName)
                ->where('department_id', $department->id)
                ->exists();

            if (!$positionExists) {
                $validator->errors()->add(
                    'position_name',
                    'The selected position does not belong to the selected department.'
                );
            }
        });
    }
}
