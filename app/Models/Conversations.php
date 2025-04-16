<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Clients;
use App\Models\Conversations_Messages;

class Conversations extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    public function messages()
    {
        return $this->hasMany(Conversations_Messages::class, 'conversation_id');
    }
}