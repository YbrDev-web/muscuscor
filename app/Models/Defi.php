<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defi extends Model
{
    protected $fillable = [
        'nom',
        'niveau_difficulte',
        'type',
    ];

    public function utilisateurs()
    {
        return $this->belongsToMany(User::class, 'defis_utilisateurs')
                    ->withPivot('statut')
                    ->withTimestamps();
    }
}
