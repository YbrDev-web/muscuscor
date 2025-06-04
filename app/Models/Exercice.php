<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'duree',
        'type',
        'niveau'
    ];

    public function performances()
    {
        return $this->hasMany(Performance::class);
    }
}

