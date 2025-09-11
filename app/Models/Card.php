<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_type_id', // store actual ID
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
        'profile_image_url'
    ];

    protected $casts = [
        'block' => 'array',
    ];

    // Accessor for formatted card_type_id
    public function getCardTypeIdAttribute($value)
    {
        return str_pad($value, 6, '0', STR_PAD_LEFT);
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
}
