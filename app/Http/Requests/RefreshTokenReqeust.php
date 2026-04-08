<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefreshTokenReqeust extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all users to attempt a refresh
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // No body input is expected, so rules are empty
        return [];
    }

    /**
     * Add custom validation for cookie existence.
     */
    protected function prepareForValidation()
    {
        if (!$this->cookie('refresh_token')) {
            // Force validation failure if cookie is missing
            $this->merge(['refresh_token_missing' => true]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('refresh_token_missing')) {
                $validator->errors()->add('refresh_token', 'Refresh token cookie is required.');
            }
        });
    }
}
