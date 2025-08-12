<?php

namespace App\Repository;

use App\Models\Card;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class CardRepository
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Step 1: Get the latest card_type_id for this type
            $lastId = Card::where('card_type', $data['card_type'])
                ->lockForUpdate()
                ->max('card_type_id') ?? 0;

            $newTypeId = $lastId + 1;

            // Step 2: Create card with card_type_id
            $card = new Card();
            $card->card_type_id = $newTypeId;
            $card->card_type = $data['card_type'];
            $card->card_name = $data['card_name'];
            $card->block = $data['block'];
            $card->user_id = $data['user_id'];
            $card->save();

            // Step 3: Handle profile image if provided
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

            return $card;
        });
    }

    public function getAllCards()
    {
        return Card::with('user')->latest()->take(17)->get()->map(function ($card) {
            $card->create_by = $card->user->name;
            $card->makeHidden('user');
            return $card;
        });
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
        $results = Card::when(!empty($searchByName), function ($query) use ($searchByName) {
            $query->where('card_name', 'like', '%' . $searchByName . '%');
        })
            ->get()
            ->filter(function ($card) use ($filter, $filterValue) {
                if ($filter === 'block') {
                    $blocks = json_decode($card->block, true) ?? [];
                    return in_array($filterValue, $blocks);
                }
                if ($filter === 'card_type') {
                    return $card->card_type === $filterValue;
                }
                return true;
            });

        return $results->values(); // reset array keys
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
