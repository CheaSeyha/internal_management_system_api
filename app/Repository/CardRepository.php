<?php

namespace App\Repository;

use App\Models\Card;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CardRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function store($cardRequest)
    {
        // Create a new card using the validated data
        $card = new Card();
        $card->card_type = $cardRequest['card_type'];
        $card->card_name = $cardRequest['card_name'];
        $card->block = $cardRequest['block'];
        $card->user_id = $cardRequest['user_id'];

        // Save the card first to generate an ID
        $card->save();

        if (isset($cardRequest['profile_image'])) {
            // Generate filename: {id}-{sanitized-card-name}.{ext}
            $file = $cardRequest['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($cardRequest['card_name']);
            $filename = "card_id={$card->id}-card_name={$sanitizedName}.{$extension}";

            // Store with custom filename
            $card->profile_image = $file->storeAs(
                'cards/profile_images',  // Using forward slash for compatibility
                $filename,
                'public'
            );

            // Save again with the image path
            $card->save();
        }

        return $card;
    }

    public function getAllCards()
    {
        $cards = Card::with('user')->get();

        $cards->each(function ($card) {
            // Add creator name and hide user object
            $card->create_by = $card->user->name;
            $card->makeHidden('user');

            // Transform profile image to full URL if exists
            if ($card->profile_image) {
                $card->profile_image_url = Storage::disk('public')->url($card->profile_image);
                $card->makeHidden('profile_image'); // Hide the original path
            }
        });

        return $cards;
    }

    public function getCardById($id)
    {
        $card = Card::with('user')->find($id);

        if ($card) {
            // Add creator name and hide user object
            $card->create_by = $card->user->name;
            $card->makeHidden('user');

            // Transform profile image to full URL if exists
            if ($card->profile_image) {
                $card->profile_image_url = Storage::disk('public')->url($card->profile_image);
                $card->makeHidden('profile_image'); // Hide the original path
            }

            return $card;
        }

        return null;
    }

    public function updateCard($id, $cardRequest)
    {
        $card = Card::find($id);

        if (!$card) {
            return null; // or throw new \Exception("Card not found");
        }

        $card->card_type = $cardRequest['card_type'];
        $card->card_name = $cardRequest['card_name'];
        $card->block = $cardRequest['block'];

        if (isset($cardRequest['profile_image'])) {
            // Delete the old image if it exists
            if ($card->profile_image) {
                Storage::disk('public')->delete($card->profile_image);
            }

            // Generate unique filename: {id}-{sanitized-card-name}.{ext}
            $file = $cardRequest['profile_image'];
            $extension = $file->getClientOriginalExtension();
            $sanitizedName = Str::slug($cardRequest['card_name']);
            $filename = "card_id={$id}-card_name={$sanitizedName}.{$extension}";

            // Store with custom filename
            $card->profile_image = $file->storeAs(
                'cards/profile_images',
                $filename,
                'public'
            );
        }

        $card->user_id = $cardRequest['user_id'];
        $card->save();

        return $card;
    }

}
