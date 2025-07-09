<?php

namespace App\Repository;

use App\Models\Card;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CardRepository
{
    public function store(array $data)
    {
        $card = new Card();
        $card->card_type = $data['card_type'];
        $card->card_name = $data['card_name'];
        $card->block = $data['block'];
        $card->user_id = $data['user_id'];

        $card->save();

        if (isset($data['profile_image'])) {
            $file = $data['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($data['card_name']);
            $filename = "card_id={$card->id}-card_name={$sanitizedName}.{$extension}";

            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'private'
            );
            $card->save();
        }

        return $card;
    }

    public function getAllCards()
    {
        return Card::with('user')->get()->map(function ($card) {
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

    public function updateCard($id, array $data)
    {
        $card = Card::find($id);

        if (!$card) {
            return null;
        }

        $card->card_type = $data['card_type'];
        $card->card_name = $data['card_name'];
        $card->block = $data['block'];

        if (isset($data['profile_image'])) {
            if ($card->profile_image) {
                Storage::disk('private')->delete($card->profile_image);
            }

            $file = $data['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($data['card_name']);
            $filename = "card_id={$id}-card_name={$sanitizedName}.{$extension}";

            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'private'
            );
        }

        $card->save();

        return $card;
    }

    public function deleteCard($cardId)
    {
        $card = Card::find($cardId);

        if (!$card) {
            return false;
        }

        if ($card->profile_image) {
            Storage::disk('private')->delete($card->profile_image);
        }

        $card->delete();
        return true;
    }
}