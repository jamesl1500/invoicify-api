<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    //
    protected $table = 'invoices';

    protected $fillable = [
        'id',
        'user_id',
        'client_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'status',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'notes',
        'terms',
        'pdf_url'
    ];

    // The primary key is a UUID
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
     * This invoice belongs to one user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * client()
     * ---------
     * This invoice belongs to one client
     */
    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    /**
     * payments()
     * ----------
     * This invoice has many payments
     */
    public function payments()
    {
        return $this->hasMany(Payments::class);
    }

    /**
     * items()
     * -------
     * This invoice has many items
     */
    public function items()
    {
        return $this->hasMany(Invoices_Items::class, 'invoice_id', 'id');
    }

    /**
     * notifications()
     * ---------------
     * This invoice has many notifications
     */
    public function notifications()
    {
        return $this->hasMany(Invoice_Activity::class, 'invoice_id', 'id');
    }
}
