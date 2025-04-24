<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRole extends Model
{
    protected $table = 'access_role';

    protected $fillable = [
        'name',
        'slug'
    ];

    public function accesses()
    {
        return $this->hasMany(Access::class, 'role_id');
    }
}
