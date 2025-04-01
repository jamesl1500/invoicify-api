<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Invoices;
use App\Models\Payments;

class Clients extends Model
{
    //
    protected $table = 'clients';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'email',
        'phone',
        'address'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
    ];

    /**
     * boot()
     * ------
     * This method is called when the model is being created
     */
    protected static function boot()
    {
        parent::boot();

        // Generate a UUID for the primary key
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
        });
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
        return $this->hasMany(Invoices::class);
    }

    /**
     * payments()
     * ----------
     * This client has many payments
     */
    public function payments()
    {
        return $this->hasMany(Payments::class);
    }
}
