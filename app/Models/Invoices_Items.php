<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoices_Items extends Model
{
    //
    protected $table = 'invoices__items';
    protected $fillable = [
        'id',
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
    ];

    // Use UUIDs for the primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
    ];

    // Automatically generate UUIDs for the primary key
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * invoice()
     * -----------
     * This invoice item belongs to an invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_id', 'id');
    }
}
