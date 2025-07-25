<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'bio', 'date_naissance',
        'sexe', 'poids', 'taille', 'niveau', 'objectifs', 'profil_public'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_naissance' => 'date',
        'poids' => 'decimal:2',
        'objectifs' => 'array',
        'profil_public' => 'boolean',
        'derniere_activite' => 'datetime',
    ];

    // Relations
    public function performances()
    {
        return $this->hasMany(Performance::class);
    }

    public function defisCreated()
    {
        return $this->hasMany(Defi::class, 'createur_id');
    }

    public function defisParticipated()
    {
        return $this->belongsToMany(Defi::class, 'defi_participants')
                    ->withPivot('date_inscription', 'progression', 'objectif_atteint', 'date_completion')
                    ->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
                    ->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
                    ->withTimestamps();
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'badge_user')
                    ->withPivot('date_obtention')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotNull('derniere_activite')
                    ->where('derniere_activite', '>=', now()->subDays(30));
    }

    // Accessors & Mutators
    public function getAgeAttribute()
    {
        return $this->date_naissance ? $this->date_naissance->age : null;
    }

    public function getImcAttribute()
    {
        if ($this->poids && $this->taille) {
            return round($this->poids / (($this->taille / 100) ** 2), 1);
        }
        return null;
    }

    // MÃ©thodes helper
    public function isFollowing(User $user)
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function hasLikedPost(Post $post)
    {
        return $this->likedPosts()->where('post_id', $post->id)->exists();
    }
}

// app/Models/Exercice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exercice extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'description', 'type', 'niveau', 'muscle_principal',
        'muscles_secondaires', 'equipement', 'instructions', 'image', 'is_active'
    ];

    protected $casts = [
        'muscles_secondaires' => 'array',
        'is_active' => 'boolean',
    ];

    public function performances()
    {
        return $this->hasMany(Performance::class);
    }

    public function defis()
    {
        return $this->hasMany(Defi::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByNiveau($query, $niveau)
    {
        return $query->where('niveau', $niveau);
    }

    public function getPerformanceStatsAttribute()
    {
        return [
            'total_sessions' => $this->performances()->count(),
            'poids_max' => $this->performances()->max('poids_max'),
            'repetitions_max' => $this->performances()->max('repetitions_max'),
        ];
    }
}

// app/Models/Performance.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Performance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'exercice_id', 'date_performance', 'series',
        'duree_totale', 'notes', 'poids_max', 'repetitions_max'
    ];

    protected $casts = [
        'date_performance' => 'date',
        'series' => 'array',
        'poids_max' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForExercice($query, $exerciceId)
    {
        return $query->where('exercice_id', $exerciceId);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_performance', [$startDate, $endDate]);
    }

    public function getVolumeotalAttribute()
    {
        if (!$this->series) return 0;
        
        return collect($this->series)->sum(function ($serie) {
            return ($serie['poids'] ?? 0) * ($serie['repetitions'] ?? 0);
        });
    }

    public function getNombreSeriesAttribute()
    {
        return $this->series ? count($this->series) : 0;
    }
}

// app/Models/Defi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Defi extends Model
{
    use HasFactory;

    protected $fillable = [
        'createur_id', 'titre', 'description', 'type', 'objectif',
        'exercice_id', 'date_debut', 'date_fin', 'max_participants',
        'statut', 'regles', 'image'
    ];

    protected $casts = [
        'objectif' => 'array',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'regles' => 'array',
    ];

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'defi_participants')
                    ->withPivot('date_inscription', 'progression', 'objectif_atteint', 'date_completion')
                    ->withTimestamps();
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif')
                    ->where('date_debut', '<=', now())
                    ->where('date_fin', '>=', now());
    }

    public function scopeDisponible($query)
    {
        return $query->where('statut', 'actif')
                    ->where('date_debut', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('max_participants')
                          ->orWhereRaw('(SELECT COUNT(*) FROM defi_participants WHERE defi_id = defis.id) < max_participants');
                    });
    }

    public function getEstTermineAttribute()
    {
        return $this->date_fin < now() || $this->statut === 'termine';
    }

    public function getPlacesRestantesAttribute()
    {
        if (!$this->max_participants) return null;
        return $this->max_participants - $this->participants()->count();
    }
}

// app/Models/Post.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'titre', 'contenu', 'image', 'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getCommentairesCountAttribute()
    {
        return $this->commentaires()->count();
    }
}