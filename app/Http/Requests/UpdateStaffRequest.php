<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Position;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staffId = $this->route('staff_id');
        $staff = Staff::where('id', $staffId)->first();

        return [
            'staff_id' => [
                'sometimes',
                'integer',
                Rule::unique('staff', 'id')->ignore($staff?->id),
            ],

            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name'  => ['sometimes', 'string', 'max:100'],

            'label_id'   => ['sometimes', 'string', 'max:50'],

            'gender'     => ['sometimes', 'in:male,female,other'],

            'email'      => [
                'sometimes',
                'email',
                Rule::unique('staff', 'email')->ignore($staff?->id),
            ],

            'phone_number' => ['sometimes', 'string', 'max:20'],

            'department_id' => [
                'sometimes',
                Rule::exists('departments', 'id'),
            ],

            'position_id' => [
                'sometimes',
                Rule::exists('positions', 'id'),
            ],

            'isCreatedUser' => ['sometimes', 'boolean'],

            'role_name' => [
                'required_if:isCreatedUser,true',
                'sometimes',
                Rule::exists('roles', 'role_name'),
            ],

            'status' => [
                'sometimes',
                Rule::in(['working', 'resigned', 'terminated', 'probation', 'walkout']),
            ],

            'date_of_joining' => ['sometimes', 'date'],
            'date_of_birth'   => ['sometimes', 'date'],

            'profile_picture' => ['sometimes', 'file', 'image', 'max:2048'],
        ];
    }
}
