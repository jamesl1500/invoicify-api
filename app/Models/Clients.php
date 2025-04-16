<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticable;

use App\Models\User;
use App\Models\Invoices;
use App\Models\Payments;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Conversations;

class Clients extends Authenticable
{
    use HasApiTokens, Notifiable;

    //
    protected $table = 'clients';
    protected $guard = 'client';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'email',
        'password',
        'phone',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversations
     */
    public function conversations()
    {
        return $this->hasMany(Conversations::class, 'client_id');
    }

    /**
     * user()
     * --------
     * This client belongs to one user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * invoices()
     * ----------
     * This client has many invoices
     */
    public function invoices()
    {
        return $this->hasMany(Invoices::class, 'client_id');
    }

    /**
     * payments()
     * ----------
     * This client has many payments
     */
    public function payments()
    {
        return $this->hasMany(Payments::class, 'client_id');
    }
}
