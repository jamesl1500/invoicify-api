<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    //
    protected $table = 'payments';

    protected $fillable = [
        'id',
        'invoice_id',
        'client_id',
        'user_id',
        'amount',
        'payment_date',
        'payment_method',
        'status',
        'amount',
        'transaction_id'
    ];

    // The primary key is a UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
        'payment_date' => 'datetime',
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
     * invoice()
     * ---------
     * This payment belongs to one invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_id', 'id');
    }

    /**
     * user()
     * ------
     * This payment belongs to one user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * client()
     * --------
     * This payment belongs to one client
     */
    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    
}
