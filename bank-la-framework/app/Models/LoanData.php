<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanData extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','principal', 'interest', 'fixed_interest_rate','duration','duration_type','next_of_kin','next_of_kin_phone','approved_date','due_date','status', 'total_amount', 'created_at', 'updated_at'];
}
