<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    protected $table = 'access';

    protected $fillable = [
        'role_id',
        'crud',
    ];
    protected $casts = [
        'crud' => 'array', // Automatically casts JSON to array
    ];

    public function module()
    {
        return $this->belongsTo(AccessModule::class, 'access_module_id');
    }

    public function role()
    {
        return $this->belongsTo(AccessRole::class, 'role_id');
    }
}
