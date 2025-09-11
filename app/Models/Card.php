<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_card_id',
        'card_type',
        'card_name',
        'block',
        'profile_image',
        'user_id',
    ];

    protected $hidden = [
        'user_id',
        'updated_at',
        'created_at',
        'profile_image' // Hide the actual path from JSON responses
    ];

    protected $appends = [
        'profile_image_url'
    ];

    protected $casts = [
        'block' => 'array',
    ];


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

    // Accessor for formatted card_type_id
    public function getCardTypeIdAttribute($value)
    {
        return str_pad($value, 6, '0', STR_PAD_LEFT);
    }
}
