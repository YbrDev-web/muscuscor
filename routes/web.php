<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExerciceController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\DefiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;


Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/exercices', [ExerciceController::class, 'index'])->name('exercices.index');
    Route::get('/exercices/create', [ExerciceController::class, 'create'])->name('exercices.create');
    Route::post('/exercices', [ExerciceController::class, 'store'])->name('exercices.store');
    Route::get('/performances', [PerformanceController::class, 'index'])->name('performances.index');
    Route::get('/performances/create', [PerformanceController::class, 'create'])->name('performances.create');
    Route::post('/performances', [PerformanceController::class, 'store'])->name('performances.store');
    Route::get('/defis', [DefiController::class, 'index'])->name('defis.index');
    Route::get('/defis/create', [DefiController::class, 'create'])->name('defis.create');
    Route::post('/defis', [DefiController::class, 'store'])->name('defis.store');
    Route::get('/defis/{defi}/show', [DefiController::class, 'show'])->name('defis.show');
    Route::post('/defis/{defi}/participer', [DefiController::class, 'participer'])->name('defis.participer');
    Route::resource('posts', PostController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('crud', CrudController::class);
});

require __DIR__.'/auth.php';