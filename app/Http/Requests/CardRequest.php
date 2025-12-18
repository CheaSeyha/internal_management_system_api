<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Building;
use App\Models\Isp;
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

        if ($this->input('card_type') === 'isp') {
            $rules['isp_name'] = [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!Isp::where('isp_name', $value)->exists()) {
                        $fail('The selected ISP does not exist.');
                    }
                }
            ];

            $rules['isp_position'] = 'required|string|max:255';
        }
        if ($this->input('card_type') === 'rolling') {
            $rules['link'] = 'required|string|max:255';
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
