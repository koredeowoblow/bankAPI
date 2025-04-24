<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    // public $timestamps = false; // Because your table uses date_created instead of created_at

    protected $table = 'transactions';
      // Specify the table name if it's not the default

    protected $fillable = [
        'reference_number',
        'transaction_type',
        'amount',
        'sender_bank_detail',
        'recipient_bank_details',
        'user_id',
        'funding_details',
        'transaction_nature',
        'created_at',
    ];


    // If you have an Account or User model linked to this
    public function account()
    {
        return $this->belongsTo(User::class, 'account_id');
    }
}
