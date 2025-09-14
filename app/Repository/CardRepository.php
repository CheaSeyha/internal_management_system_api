<?php

namespace App\Repository;

use App\Models\Building;
use App\Models\Card;
use App\Models\CardType;
use App\Models\Room;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class CardRepository
{
    public function store(array $data)
    {
        // 1️⃣ Get or create CardType
        $cardType = CardType::where('name', $data['card_type'])->firstOrFail();


        // 2️⃣ Determine next card number
        $lastNumber = Card::where('card_type_id', $cardType->id)->max('card_number') ?? 0;
        $nextNumber = $lastNumber + 1;

        // 3️⃣ Create card
        $card = Card::create([
            'card_type_id' => $cardType->id,
            'card_number'  => $nextNumber,
            'card_name'    => $data['card_name'],
            'user_id'      => auth()->id(),
        ]);
        // Store iamge if provided
        if (isset($data['profile_image'])) {
            $file = $data['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($data['card_name']);
            $filename = "card_type={$data['card_type']},card_id={$nextNumber},card_name={$sanitizedName}.{$extension}";

            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'private'
            );
        }

        // 4️⃣ Parse blocks and attach
        $blocks = collect(explode(',', $data['block'] ?? ''))
            ->map(function ($block) {
                $parts = explode('-', $block);
                $buildingName = $parts[0];
                $roomNames = array_slice($parts, 1); // all rooms after building

                $building = Building::where('building_name', $buildingName)->first();
                if (!$building) return null;

                // Fetch all room IDs for these rooms
                $roomIds = Room::where('building_id', $building->id)
                    ->whereIn('room_name', $roomNames)
                    ->pluck('id')
                    ->toArray();

                // If no rooms, set room_id null for building-only
                if (empty($roomIds)) {
                    return [
                        'building_id' => $building->id,
                        'room_ids'    => [null],
                        'label'       => $block,
                    ];
                }

                return [
                    'building_id' => $building->id,
                    'room_ids'    => $roomIds,
                    'label'       => $block,
                ];
            })
            ->filter();



        foreach ($blocks as $block) {
            foreach ($block['room_ids'] as $roomId) {
                $card->buildings()->attach($block['building_id'], [
                    'room_id' => $roomId
                ]);
            }
        }


        // 5️⃣ Prepare response
        $user = auth()->user();
        $blockString = $blocks->pluck('label')->join(','); // combine all blocks
        $card->save();
        return $card->toResponse();
    }


    public function getAllCards()
    {
        // Load cards with relationships
        $cards = Card::with(['user', 'cardType', 'buildings.rooms'])
            ->latest()
            ->paginate(17);

        // Transform each Card model using toResponse()
        $cards->getCollection()->transform(function ($card) {
            return $card->toResponse();
        });

        return $cards;
    }



    public function cardsFilter($searchByName = null, $filter = null, $filterValue = null,)
    {
        $query = Card::with(['user', 'cardType', 'buildings.rooms'])->latest();

        // 🔍 Search by name
        if ($searchByName) {
            $query->where(function ($q) use ($searchByName) {
                $q->where('card_name', 'like', '%' . $searchByName . '%')
                    ->orWhere('card_number', $searchByName); // exact match for number
            });
        }




        // 🔍 Filter by card type
        if ($filter === 'card_type' && $filterValue) {
            $query->whereHas('cardType', function ($q) use ($filterValue) {
                $q->where('name', $filterValue);
            });
        }

        // 🔍 Filter by block (building name)
        if ($filter === 'block' && $filterValue) {
            $query->whereHas('buildings', function ($q) use ($filterValue) {
                $q->where('building_name', $filterValue);
            });
        }

        // Paginate results
        $cards = $query->paginate(17);

        if ($cards->isEmpty()) {
            return false; // Return false if no cards found
        }

        // Keep filter & search in pagination URLs
        $cards->appends([
            'searchByName' => $searchByName,
            'filter' => $filter,
            'filterValue' => $filterValue
        ]);

        // Transform items in the paginator
        $cards->getCollection()->transform(function ($card) {
            $blockString = $card->buildings->map(function ($building) {
                $pivotRoomId = $building->pivot->room_id;
                $roomName = $building->rooms->firstWhere('id', $pivotRoomId)->room_name ?? null;

                return $roomName
                    ? "{$building->building_name}-{$roomName}"
                    : $building->building_name;
            })->join(', ');

            return [
                'id'               => $card->id,
                'card_type_id'     => $card->getFormattedCardNumberAttribute(),
                'card_type'        => strtoupper($card->cardType->name ?? null),
                'card_name'        => $card->card_name,
                'block'            => $blockString,
                'create_by'        => $card->user->name ?? null,
                'profile_image_url' => $card->profile_image_url,
            ];
        });

        return $cards;
    }

    //not use
    public function getCardById($id)
    {
        $card = Card::with('user')->find('card_number', $id);

        if ($card) {
            $card->create_by = $card->user->name;
            $card->makeHidden('user');
        }

        return $card;
    }

    public function getCardByIDAndCardType(int $typeCardId, string $card_type)
    {
        $card = Card::with('user', 'cardType')
            ->where('card_number', $typeCardId)
            ->whereHas('cardType', function ($q) use ($card_type) {
                $q->where('name', $card_type);
            })
            ->first();

        if ($card) {
            $card->create_by = $card->user->name ?? null;
            $card->makeHidden('user');
        }

        return $card;
    }

    // not use 
    public function getCardByNameAndCardType(string $card_name, ?string $card_type = null)
    {
        $cards = Card::with('user')
            ->when($card_type, function ($query, $card_type) {
                return $query->where('card_type', $card_type);
            })
            ->where('card_name', 'like', '%' . $card_name . '%')
            ->get();

        if ($cards->isEmpty()) {
            return false; // or null, depending on your convention
        }

        //show user name that create the card
        foreach ($cards as $card) {
            $card->create_by = $card->user->name;
            $card->makeHidden('user');
        }

        return $cards;
    }

    //not use
    public function getAllCardType()
    {
        $cardTypes = CardType::all();

        if ($cardTypes->isEmpty()) {
            return false;
        }

        return $cardTypes->map(function ($type) {
            return [
                'card_type'  => strtoupper($type->name),
                'count' => $type->cards()->count(), // Count cards by type
            ];
        });
    }



    public function getCardImage($cardId)
    {
        $card = Card::find($cardId);
        return $card && $card->profile_image
            ? [
                'path' => $card->profile_image,
                'mime_type' => Storage::disk('private')->mimeType($card->profile_image)
            ]
            : null;
    }

    public function updateCard($card_type_id, $card_type, array $data)
    {
        $isCardExist = $this->getCardByIDAndCardType($card_type_id, $card_type);

        if (!$isCardExist) {
            return false;
        }

        $card = Card::find($isCardExist['id']);
        if (!$card) {
            return false;
        }

        // 1️⃣ Update card type if provided
        if (isset($data['card_type'])) {
            $newCardType = CardType::where('name', $data['card_type'])->first();
            if ($newCardType) {
                $card->card_type_id = $newCardType->id;

                // 1a️⃣ Check card_number uniqueness within new card_type
                $exists = Card::where('card_type_id', $newCardType->id)
                    ->where('card_number', $card->card_number)
                    ->where('id', '!=', $card->id)
                    ->exists();

                if ($exists) {
                    // Assign next available card_number for this card_type
                    $lastNumber = Card::where('card_type_id', $newCardType->id)->max('card_number') ?? 0;
                    $card->card_number = $lastNumber + 1;
                }
            }
        }

        // 2️⃣ Update card name if provided
        if (isset($data['card_name'])) {
            $card->card_name = $data['card_name'];
        }

        // 3️⃣ Update profile image if provided
        if (isset($data['profile_image'])) {
            if ($card->profile_image) {
                Storage::disk('private')->delete($card->profile_image);
            }

            $file = $data['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($data['card_name'] ?? $card->card_name);
            $filename = "card_type={$data['card_type']},card_id={$card->card_number},card_name={$sanitizedName}.{$extension}";

            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'private'
            );
        }

        // 4️⃣ Update blocks if provided
        // 4️⃣ Update blocks if provided
        if (isset($data['block'])) {
            // Detach existing building-room relationships
            $card->buildings()->detach();

            $blocks = collect(explode(',', $data['block'] ?? ''))
                ->map(function ($block) {
                    $parts = explode('-', $block);
                    $buildingName = $parts[0];
                    $roomNames = array_slice($parts, 1); // all rooms after building

                    $building = Building::where('building_name', $buildingName)->first();
                    if (!$building) return null;

                    // Fetch all room IDs for these rooms
                    $roomIds = Room::where('building_id', $building->id)
                        ->whereIn('room_name', $roomNames)
                        ->pluck('id')
                        ->toArray();

                    // If no rooms, set room_id null for building-only
                    if (empty($roomIds)) {
                        return [
                            'building_id' => $building->id,
                            'room_ids'    => [null],
                            'label'       => $block,
                        ];
                    }

                    return [
                        'building_id' => $building->id,
                        'room_ids'    => $roomIds,
                        'label'       => $block,
                    ];
                })
                ->filter();

            foreach ($blocks as $block) {
                foreach ($block['room_ids'] as $roomId) {
                    $card->buildings()->attach($block['building_id'], [
                        'room_id' => $roomId
                    ]);
                }
            }
        }


        // 5️⃣ Save and return
        $card->save();

        return $card->toResponse();
    }





    public function deleteCard($card_type_id, $card_type): bool
    {
        $isCardExist = $this->getCardByIDAndCardType($card_type_id, $card_type);

        if (!$isCardExist) {
            return false;
        }

        $card = Card::find($isCardExist['id']);

        if (!$card) {
            return false;
        }

        if ($card->profile_image) {
            Storage::disk('private')->delete($card->profile_image);
        }

        $card->delete(); // triggers cascade in DB
        return true;
    }
}
