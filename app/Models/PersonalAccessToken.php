<?php
namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $casts = [
        'abilities' => 'json',       // Make sure abilities are stored as JSON
        'expires_at' => 'datetime',
    ];
}