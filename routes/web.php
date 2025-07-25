<?php
// routes/web.php

use App\Http\Controllers\{
    ProfileController, ExerciceController, PerformanceController,
    DefiController, PostController, CategoryController, CrudController,
    DashboardController, FollowController, LikeController, CommentaireController
};
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Routes d'authentification (déjà incluses via auth.php)
require __DIR__.'/auth.php';

// Groupe de routes nécessitant une authentification
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard avec données personnalisées
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion du profil
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
        Route::get('/{user}', 'show')->name('show'); // Voir le profil d'un autre utilisateur
    });
    
    // Gestion des exercices
    Route::resource('exercices', ExerciceController::class);
    Route::get('exercices/search', [ExerciceController::class, 'search'])->name('exercices.search');
    
    // Gestion des performances
    Route::controller(PerformanceController::class)->prefix('performances')->name('performances.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{performance}', 'show')->name('show');
        Route::get('/{performance}/edit', 'edit')->name('edit');
        Route::patch('/{performance}', 'update')->name('update');
        Route::delete('/{performance}', 'destroy')->name('destroy');
        
        // Routes spécifiques pour les statistiques
        Route::get('/stats/general', 'statsGeneral')->name('stats.general');
        Route::get('/stats/exercice/{exercice}', 'statsExercice')->name('stats.exercice');
        Route::get('/export', 'export')->name('export');
    });
    
    // Gestion des défis
    Route::controller(DefiController::class)->prefix('defis')->name('defis.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{defi}', 'show')->name('show');
        Route::get('/{defi}/edit', 'edit')->name('edit');
        Route::patch('/{defi}', 'update')->name('update');
        Route::delete('/{defi}', 'destroy')->name('destroy');
        
        // Actions spécifiques aux défis
        Route::post('/{defi}/participer', 'participer')->name('participer');
        Route::delete('/{defi}/quitter', 'quitter')->name('quitter');
        Route::post('/{defi}/progression', 'updateProgression')->name('progression.update');
        Route::get('/mes-defis', 'mesDefis')->name('mes-defis');
        Route::get('/disponibles', 'disponibles')->name('disponibles');
    });
    
    // Réseau social - Posts
    Route::resource('posts', PostController::class);
    Route::controller(PostController::class)->prefix('posts')->name('posts.')->group(function () {
        Route::get('/feed', 'feed')->name('feed'); // Fil d'actualités personnalisé
        Route::get('/mes-posts', 'mesPosts')->name('mes-posts');
        Route::post('/{post}/toggle-publish', 'togglePublish')->name('toggle-publish');
    });
    
    // Gestion des likes
    Route::controller(LikeController::class)->prefix('likes')->name('likes.')->group(function () {
        Route::post('/posts/{post}', 'togglePostLike')->name('post.toggle');
    });
    
    // Gestion des commentaires
    Route::resource('commentaires', CommentaireController::class)->except(['index', 'show']);
    
    // Système de suivi (follow/unfollow)
    Route::controller(FollowController::class)->prefix('follow')->name('follow.')->group(function () {
        Route::post('/{user}', 'follow')->name('user');
        Route::delete('/{user}', 'unfollow')->name('unfollow');
        Route::get('/followers', 'followers')->name('followers');
        Route::get('/following', 'following')->name('following');
    });
    
    // Gestion des catégories (admin seulement)
    Route::middleware('admin')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('crud', CrudController::class);
        
        // Routes d'administration
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/users', [AdminController::class, 'users'])->name('users');
            Route::get('/stats', [AdminController::class, 'stats'])->name('stats');
        });
    });
    
    // API Routes pour AJAX
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/exercices/search', [ExerciceController::class, 'apiSearch'])->name('exercices.search');
        Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
        Route::get('/performances/chart-data/{exercice}', [PerformanceController::class, 'chartData'])->name('performances.chart-data');
        Route::get('/defis/{defi}/leaderboard', [DefiController::class, 'leaderboard'])->name('defis.leaderboard');
    });
});

// Routes publiques pour les profils publics
Route::get('/u/{user:name}', [ProfileController::class, 'publicProfile'])->name('profile.public');
Route::get('/posts/{post:slug}', [PostController::class, 'publicShow'])->name('posts.public');