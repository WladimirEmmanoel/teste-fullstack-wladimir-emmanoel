<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    protected $fillable = [
        'external_id',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
