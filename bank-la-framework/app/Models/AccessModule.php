<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessModule extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'link',
        'style',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(AccessModule::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccessModule::class, 'parent_id');
    }
}
