<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Building;
use App\Models\Room;

class CardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation.
     * Decode JSON string to array if needed.
     */
    protected function prepareForValidation()
    {
        if ($this->has('block') && is_string($this->block)) {
            $decoded = json_decode($this->block, true);
            $this->merge([
                'block' => is_array($decoded) ? $decoded : [],
            ]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'card_type'     => 'required|string|exists:card_types,name',
            'card_name'     => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id'       => 'sometimes|exists:users,id',
        ];

        // Only require block if card_type is NOT rolling
        if (strtolower($this->input('card_type')) !== 'rolling') {
            $rules['block'] = 'required|array|min:1';
            $rules['block.*.building'] = 'required|string|exists:buildings,building_name';
            $rules['block.*.rooms'] = 'nullable|array';
            $rules['block.*.rooms.*'] = 'string';
        } else {
            // Optional (if you want to accept block=null without errors)
            $rules['block'] = 'nullable|array';
        }

        return $rules;
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $blocks = $this->input('block', []);
            if (!is_array($blocks)) {
                return;
            }

            foreach ($blocks as $index => $block) {
                $buildingName = $block['building'] ?? null;
                $roomNames = $block['rooms'] ?? [];

                $building = Building::where('building_name', $buildingName)->first();
                if (!$building) {
                    $validator->errors()->add(
                        "block.$index.building",
                        "Building '$buildingName' does not exist."
                    );
                    continue;
                }

                foreach ($roomNames as $roomIndex => $roomName) {
                    $roomExists = Room::where('building_id', $building->id)
                        ->where('room_name', $roomName)
                        ->exists();

                    if (!$roomExists) {
                        $validator->errors()->add(
                            "block.$index.rooms.$roomIndex",
                            "Room '$roomName' does not exist in building '$buildingName'."
                        );
                    }
                }
            }
        });
    }

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
