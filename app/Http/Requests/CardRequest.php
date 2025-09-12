<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust if you want role-based auth
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 🔹 Validate that card_type exists in the card_types table
            'card_type'     => 'required|string|exists:card_types,name',
            'card_name'     => 'required|string|max:255',
            'block'         => 'required|string|',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id'       => 'sometimes|exists:users,id'
        ];
    }

    /**
     * Customize failed validation response
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
