<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = ['nom', 'description', 'image'];

    public function utilisateurs()
    {
        return $this->belongsToMany(User::class, 'badge_user')->withTimestamps();
    }

}
