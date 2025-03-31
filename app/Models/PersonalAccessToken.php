<?php
namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $keyType = 'string';  // Tell Eloquent it's a string (UUID)
    public $incrementing = false;   // Disable auto-incrementing

    protected $casts = [
        'id' => 'string',           // Ensure UUID is treated as a string
        'abilities' => 'array', // Cast abilities to an array
        'tokenable_id' => 'string', // Ensure the tokenable ID is treated as a string
        'expires_at' => 'datetime', // Cast expires_at to a datetime
    ];
}