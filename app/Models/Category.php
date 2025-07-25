<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Champs autorisés en mass-assignment
    protected $fillable = ['name'];

    /**
     * Une catégorie peut avoir plusieurs posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
