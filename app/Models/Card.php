<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_type',
        'card_name',
        'block',
        'user_id',
        'profile_image'
    ];

    protected $hidden = [
        'profile_image' // Hide the actual path from JSON responses
    ];

    protected $appends = [
        'profile_image_url'
    ];

    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image 
            ? route('api.cards.image', $this->id)
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}