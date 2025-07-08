<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'card_type',
        'card_name',
        'block',
        'profile_image',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
