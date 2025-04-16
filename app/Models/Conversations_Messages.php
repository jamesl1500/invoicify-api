<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversations_Messages extends Model
{
    use HasFactory;

    protected $table = 'conversation_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'body',
        'type',
        'read_at',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversations::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->morphTo();
    }
}