<?php
namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $keyType = 'string';  // Ensure UUIDs are treated as strings
    public $incrementing = false;   // Disable auto-incrementing IDs

    protected $casts = [
        'tokenable_id' => 'string',  // Ensure Laravel treats tokenable_id as a UUID string
        'abilities' => 'json',       // Make sure abilities are stored as JSON
        'expires_at' => 'datetime',
    ];
}