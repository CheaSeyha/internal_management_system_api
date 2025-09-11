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
        $cardType = CardType::firstOrCreate(['name' => $data['card_type']]);

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
            $filename = "card_type={$card->card_type}-card_type_id={$card->card_type_id}-{$sanitizedName}.{$extension}";

            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'private'
            );
            $card->save();
        }

        // 4️⃣ Parse blocks and attach
        $blocks = collect(explode(',', $data['block'] ?? ''))
            ->map(function ($block) {
                $parts = explode('-', $block);
                $building = Building::where('building_name', $parts[0])->first();
                if (!$building) return null;

                $roomId = isset($parts[1])
                    ? Room::where('room_name', $parts[1])
                    ->where('building_id', $building->id)
                    ->value('id')
                    : null;

                return [
                    'building_id' => $building->id,
                    'room_id'     => $roomId,
                    'label'       => $block, // store original string for display
                ];
            })
            ->filter();

        foreach ($blocks as $block) {
            $card->buildings()->attach($block['building_id'], ['room_id' => $block['room_id']]);
        }

        // 5️⃣ Prepare response
        $user = auth()->user();
        $blockString = $blocks->pluck('label')->join(', '); // combine all blocks

        return [
            'id'              => $card->id,
            'card_type_id'    => str_pad($card->card_number, 6, '0', STR_PAD_LEFT), // formatted card number
            'card_type'       => $cardType->name,
            'card_name'       => $card->card_name,
            'block'           => $blockString,
            'create_by'       => $user->name,
            'profile_image_url' => url("/cards/{$card->id}/image"),
        ];
    }





    public function getAllCards()
    {
        // Load cards with user and latest first, paginated
        $cards = Card::with(['user', 'buildings.rooms'])->latest()->paginate(17);

        // Transform each card
        $cards->getCollection()->transform(function ($card) {
            // Prepare block string
            $blockString = $card->buildings->map(function ($building) use ($card) {
                $pivotRoomId = $building->pivot->room_id;
                $roomName = $building->rooms->firstWhere('id', $pivotRoomId)->room_name ?? null;

                return $roomName
                    ? "{$building->building_name}-{$roomName}"
                    : $building->building_name;
            })->join(', ');

            return [
                'id'               => $card->id,
                'card_type_id'     => $card->card_type_id,
                'card_type'        => $card->cardType->name ?? null,
                'card_name'        => $card->card_name,
                'block'            => $blockString,
                'create_by'        => $card->user->name ?? null,
                'profile_image_url' => $card->profile_image_url,
            ];
        });

        return $cards;
    }



    public function getCardById($id)
    {
        $card = Card::with('user')->find($id);

        if ($card) {
            $card->create_by = $card->user->name;
            $card->makeHidden('user');
        }

        return $card;
    }

    public function getCardByIDAndCardType(int $typeCardId, string $card_type)
    {
        $card = Card::with('user')
            ->where('card_type', $card_type)
            ->where('card_type_id', $typeCardId)
            ->first();
        //show user name that create the card
        if ($card) {
            $card->create_by = $card->user->name;
            $card->makeHidden('user');
        }

        return $card;
    }

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


    public function cardsFilter($searchByName = null, $filter = null, $filterValue = null)
    {
        $query = Card::with('user');

        if ($searchByName) {
            $query->where('card_name', 'like', '%' . $searchByName . '%');
        }
        if ($filter === 'card_type' && $filterValue) {
            $query->where('card_type', $filterValue);
        }

        if ($filter === 'block' && $filterValue !== null) {
            $query->where('block', $filterValue);
        }

        // dd($query);


        $cards = $query->get();

        // // transform items in paginator
        // $cards->getCollection()->transform(function ($card) {
        //     $card->create_by = $card->user->name ?? null; // only name
        //     $card->makeHidden('user'); // remove full user
        //     return $card;
        // });

        return $cards;
    }














    public function getAllCardType()
    {
        return Card::whereNotNull('card_type')
            ->where('card_type', '!=', '')
            ->distinct()
            ->pluck('card_type');
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
        $card = Card::with('user')
            ->where('card_type', $card_type)
            ->where('card_type_id', $card_type_id)
            ->first();

        if (!$card) {
            return null; // Card not found
        }



        // Step 2: Create card with card_type_id
        $card->card_type = $data['card_type'];
        $card->card_name = $data['card_name'];
        $card->block = $data['block'];
        //Delete old image of card
        if ($card->profile_image) {
            Storage::disk('private')->delete($card->profile_image);
        }
        // add new card image
        if (isset($data['profile_image'])) {
            $file = $data['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($data['card_name']);
            $filename = "card_type={$card->card_type}-card_type_id={$card->card_type_id}-{$sanitizedName}.{$extension}";

            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'private'
            );
        }
        //complete the updated
        $card->save();

        //show create by inseat of show all user
        $card->create_by = $card->user->name;
        $card->makeHidden('user');

        return $card;
    }


    public function deleteCard($card_type_id, $card_type)
    {
        $isCardExit = $this->getCardByIDAndCardType($card_type_id, $card_type);

        if (!$isCardExit) {
            return false;
        }

        if ($isCardExit->profile_image) {
            Storage::disk('private')->delete($isCardExit->profile_image);
        }

        $isCardExit->delete();
        return true;
        // $card = Card::find($cardId);

        // if (!$card) {
        //     return false;
        // }

        // if ($card->profile_image) {
        //     Storage::disk('private')->delete($card->profile_image);
        // }

        // $card->delete();
        // return true;
    }
}
