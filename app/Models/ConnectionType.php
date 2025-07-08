<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'connection_type',
        'username',
        'password',
        'ip',
        'mask',
        'getway',
        'is_active',
        'package',
        'speed',
        'isp_id',
    ];

    /**
     * Get the ISP associated with the connection type.
     */
    public function isp()
    {
        return $this->belongsTo(Isp::class);
    }
}