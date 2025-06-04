<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defi extends Model
{
    protected $fillable = [
        'nom',
        'niveau de difficulté',
        'Type de défi',
    ];

    public function utilisateurs()
    {
        return $this->belongsToMany(User::class, 'defis_utilisateurs')
        ->withPivot('statut')
        ->withTimestamps();
    }

}
