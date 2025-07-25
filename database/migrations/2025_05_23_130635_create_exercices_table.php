<?php
// Migration pour la table exercices améliorée
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercices', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('type', ['force', 'cardio', 'flexibilite', 'endurance']);
            $table->enum('niveau', ['debutant', 'intermediaire', 'avance']);
            $table->string('muscle_principal');
            $table->json('muscles_secondaires')->nullable();
            $table->string('equipement')->nullable();
            $table->text('instructions')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'niveau']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercices');
    }
};

// Migration pour améliorer la table performances
Schema::create('performances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('exercice_id')->constrained()->onDelete('cascade');
    $table->date('date_performance');
    $table->json('series'); // [{poids: 80, repetitions: 10, repos: 90}, ...]
    $table->integer('duree_totale')->nullable(); // en secondes
    $table->text('notes')->nullable();
    $table->decimal('poids_max', 5, 2)->nullable();
    $table->integer('repetitions_max')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'date_performance']);
    $table->index(['exercice_id', 'date_performance']);
});

// Migration pour améliorer la table defis
Schema::create('defis', function (Blueprint $table) {
    $table->id();
    $table->foreignId('createur_id')->constrained('users')->onDelete('cascade');
    $table->string('titre');
    $table->text('description');
    $table->enum('type', ['poids', 'repetitions', 'duree', 'frequence']);
    $table->json('objectif'); // {type: 'poids', valeur: 100, unite: 'kg'}
    $table->foreignId('exercice_id')->nullable()->constrained()->onDelete('set null');
    $table->date('date_debut');
    $table->date('date_fin');
    $table->integer('max_participants')->nullable();
    $table->enum('statut', ['brouillon', 'actif', 'termine', 'annule'])->default('brouillon');
    $table->json('regles')->nullable();
    $table->string('image')->nullable();
    $table->timestamps();
    
    $table->index(['statut', 'date_debut', 'date_fin']);
});

// Table pivot pour la participation aux défis
Schema::create('defi_participants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('defi_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamp('date_inscription');
    $table->json('progression')->nullable(); // Données de progression
    $table->boolean('objectif_atteint')->default(false);
    $table->timestamp('date_completion')->nullable();
    $table->timestamps();
    
    $table->unique(['defi_id', 'user_id']);
});

// Table pour les likes des posts
Schema::create('post_likes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    
    $table->unique(['post_id', 'user_id']);
});

// Table pour les commentaires
Schema::create('commentaires', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('contenu');
    $table->foreignId('parent_id')->nullable()->constrained('commentaires')->onDelete('cascade');
    $table->timestamps();
    
    $table->index(['post_id', 'created_at']);
});

// Table pour les abonnements/suivis
Schema::create('follows', function (Blueprint $table) {
    $table->id();
    $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
    $table->timestamps();
    
    $table->unique(['follower_id', 'following_id']);
});

// Améliorer la table users
Schema::table('users', function (Blueprint $table) {
    $table->string('avatar')->nullable()->after('email');
    $table->text('bio')->nullable()->after('avatar');
    $table->date('date_naissance')->nullable()->after('bio');
    $table->enum('sexe', ['M', 'F', 'autre'])->nullable()->after('date_naissance');
    $table->decimal('poids', 5, 2)->nullable()->after('sexe');
    $table->integer('taille')->nullable()->after('poids'); // en cm
    $table->enum('niveau', ['debutant', 'intermediaire', 'avance'])->default('debutant')->after('taille');
    $table->json('objectifs')->nullable()->after('niveau');
    $table->boolean('profil_public')->default(true)->after('objectifs');
    $table->timestamp('derniere_activite')->nullable()->after('profil_public');
});