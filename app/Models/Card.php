<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_type_id',
        'card_name',
        'card_number',
        'block',
        'profile_image',
        'user_id',
    ];

    protected $hidden = [
        'user_id',
        'updated_at',
        'created_at',
        'profile_image',
    ];

    protected $appends = [
        'profile_image_url',
        'formatted_card_number', // new
    ];

    protected $casts = [
        'block' => 'array',
    ];

    // ✅ New accessor for formatted card number
    public function getFormattedCardNumberAttribute()
    {
        return str_pad($this->card_number, 6, '0', STR_PAD_LEFT);
    }

    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image
            ? "/cards/{$this->id}/image"
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cardType()
    {
        return $this->belongsTo(CardType::class, 'card_type_id');
    }

    public function buildings()
    {
        return $this->belongsToMany(Building::class, 'card_building_room')
            ->withPivot('room_id')
            ->withTimestamps();
    }


    public function toResponse(): array
    {
        $user = $this->user;
        $blockString = $this->buildings->map(function ($building) {
            $pivotRoomId = $building->pivot->room_id;
            $roomName = $building->rooms->firstWhere('id', $pivotRoomId)->room_name ?? null;

            return $roomName
                ? "{$building->building_name}-{$roomName}"
                : $building->building_name;
        })->join(', ');

        return [
            'id'               => $this->id,
            'card_type_id'     => $this->getFormattedCardNumberAttribute(),
            'card_type'        => $this->cardType->name ?? null,
            'card_name'        => $this->card_name,
            'block'            => $blockString,
            'create_by'        => $user->name ?? null,
            'profile_image_url' => $this->profile_image_url,
        ];
    }
}
