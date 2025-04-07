<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice_Activity extends Model
{
    //
    protected $table = 'invoice_activity';

    protected $fillable = [
        'id',
        'invoice_id',
        'user_id',
        'action',
        'description',
    ];

    /**
     * invoice()
     * ---------
     * This activity belongs to one invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_id');
    }

    /**
     * user()
     * ------
     * This activity belongs to one user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}