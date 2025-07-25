{{-- resources/views/components/layout.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MuscuScore' }} | Suivi & Défis Musculation</title>
    
    <!-- Meta tags SEO -->
    <meta name="description" content="{{ $description ?? 'Le réseau social ultime pour les passionnés de musculation. Suivi de performances, défis communautaires et plus.' }}">
    <meta name="keywords" content="musculation, fitness, suivi performance, défis, réseau social sportif">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</head>
<body class="{{ $bodyClass ?? '' }}">
    @auth
        @include('components.navigation')
    @endauth
    
    <main class="{{ $mainClass ?? 'container mx-auto px-4 py-6' }}">
        @include('components.alerts')
        
        {{ $slot }}
    </main>
    
    @auth
        @include('components.footer')
    @endauth
</body>
</html>

{{-- resources/views/components/navigation.blade.php --}}
<nav class="bg-white shadow-lg border-b">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-blue-600">
                    MuscuScore
                </a>
            </div>
            
            <!-- Menu principal -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="{{ route('performances.index') }}" class="nav-link {{ request()->routeIs('performances.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Performances
                </a>
                <a href="{{ route('exercices.index') }}" class="nav-link {{ request()->routeIs('exercices.*') ? 'active' : '' }}">
                    <i class="fas fa-dumbbell"></i> Exercices
                </a>
                <a href="{{ route('defis.index') }}" class="nav-link {{ request()->routeIs('defis.*') ? 'active' : '' }}">
                    <i class="fas fa-trophy"></i> Défis
                </a>
                <a href="{{ route('posts.index') }}" class="nav-link {{ request()->routeIs('posts.*') ? 'active' : '' }}">
                    <i class="fas fa-newspaper"></i> Communauté
                </a>
            </div>
            
            <!-- Menu utilisateur -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                </div>
                
                <!-- Menu profil -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2">
                        <img src="{{ Auth::user()->avatar ?? asset('images/default-avatar.png') }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="w-8 h-8 rounded-full">
                        <span class="hidden md:block">{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down text-sm"></i>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" 
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="fas fa-user"></i> Mon Profil
                        </a>
                        <a href="{{ route('performances.stats.general') }}" class="dropdown-item">
                            <i class="fas fa-chart-bar"></i> Mes Stats
                        </a>
                        <div class="border-t my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-red-600 w-full text-left">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- resources/views/components/performance-card.blade.php --}}
@props(['performance'])

<div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">
                {{ $performance->exercice->nom }}
            </h3>
            <p class="text-sm text-gray-500">
                {{ $performance->date_performance->format('d/m/Y') }}
            </p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ ucfirst($performance->exercice->type) }}
            </span>
        </div>
    </div>
    
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="text-center p-3 bg-gray-50 rounded-lg">
            <p class="text-2xl font-bold text-gray-800">{{ $performance->poids_max }}kg</p>
            <p class="text-sm text-gray-500">Poids max</p>
        </div>
        <div class="text-center p-3 bg-gray-50 rounded-lg">
            <p class="text-2xl font-bold text-gray-800">{{ $performance->nombre_series }}</p>
            <p class="text-sm text-gray-500">Séries</p>
        </div>
    </div>
    
    @if($performance->notes)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
            <p class="text-sm text-gray-700">{{ Str::limit($performance->notes, 100) }}</p>
        </div>
    @endif
    
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500">
            Volume: {{ number_format($performance->volume_total) }}kg
        </span>
        <a href="{{ route('performances.show', $performance) }}" 
           class="btn btn-sm btn-outline">
            Voir détails
        </a>
    </div>
</div>

{{-- resources/views/components/defi-card.blade.php --}}
@props(['defi'])

<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
    @if($defi->image)
        <img src="{{ Storage::url($defi->image) }}" 
             alt="{{ $defi->titre }}" 
             class="w-full h-48 object-cover">
    @endif
    
    <div class="p-6">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-semibold text-gray-800">
                {{ $defi->titre }}
            </h3>
            <span class="status-badge status-{{ $defi->statut }}">
                {{ ucfirst($defi->statut) }}
            </span>
        </div>
        
        <p class="text-gray-600 text-sm mb-4">
            {{ Str::limit($defi->description, 120) }}
        </p>
        
        <div class="space-y-2 mb-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-target mr-2"></i>
                Objectif: {{ $defi->objectif['valeur'] }} {{ $defi->objectif['unite'] }}
            </div>
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-calendar mr-2"></i>
                {{ $defi->date_debut->format('d/m') }} - {{ $defi->date_fin->format('d/m/Y') }}
            </div>
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-users mr-2"></i>
                {{ $defi->participants->count() }} participant(s)
                @if($defi->max_participants)
                    / {{ $defi->max_participants }}
                @endif
            </div>
        </div>
        
        @if($defi->places_restantes && $defi->places_restantes <= 5)
            <div class="bg-orange-100 border border-orange-200 rounded-lg p-2 mb-4">
                <p class="text-orange-800 text-sm font-medium">
                    ⚠️ Plus que {{ $defi->places_restantes }} place(s) !
                </p>
            </div>
        @endif
        
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <img src="{{ $defi->createur->avatar ?? asset('images/default-avatar.png') }}" 
                     alt="{{ $defi->createur->name }}" 
                     class="w-6 h-6 rounded-full">
                <span class="text-sm text-gray-500">{{ $defi->createur->name }}</span>
            </div>
            
            <a href="{{ route('defis.show', $defi) }}" 
               class="btn btn-primary btn-sm">
                Voir défi
            </a>
        </div>
    </div>
</div>

{{-- resources/views/dashboard.blade.php --}}
<x-layout title="Dashboard">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Statistiques -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                    <i class="fas fa-dumbbell text-xl"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-2xl font-bold text-gray-800">{{ $stats['performances_semaine'] }}</h4>
                    <p class="text-gray-500">Séances cette semaine</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-2xl font-bold text-gray-800">{{ $stats['defis_actifs'] }}</h4>
                    <p class="text-gray-500">Défis en cours</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                    <i class="fas fa-medal text-xl"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-2xl font-bold text-gray-800">{{ $stats['badges_total'] }}</h4>
                    <p class="text-gray-500">Badges obtenus</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-2xl font-bold text-gray-800">{{ $stats['followers_count'] }}</h4>
                    <p class="text-gray-500">Abonnés</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Dernières performances -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Dernières performances</h2>
                        <a href="{{ route('performances.index') }}" class="btn btn-outline btn-sm">
                            Voir tout
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($recentPerformances as $performance)
                        <x-performance-card :performance="$performance" />
                        @if(!$loop->last)
                            <hr class="my-4">
                        @endif
                    @empty
                        <div class="text-center py-8">
                            <i class="fas fa-dumbbell text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-500">Aucune performance enregistrée</p>
                            <a href="{{ route('performances.create') }}" class="btn btn-primary mt-4">
                                Enregistrer une performance
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Défis recommandés -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Défis recommandés</h3>
                </div>
                <div class="p-6">
                    @forelse($defisRecommandes as $defi)
                        <div class="border-b last:border-b-0 pb-4 last:pb-0 mb-4 last:mb-0">
                            <h4 class="font-medium text-gray-800 mb-2">{{ $defi->titre }}</h4>
                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($defi->description, 80) }}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">
                                    {{ $defi->participants->count() }} participant(s)
                                </span>
                                <a href="{{ route('defis.show', $defi) }}" class="btn btn-primary btn-xs">
                                    Rejoindre
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">Aucun défi disponible</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Raccourcis -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Actions rapides</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('performances.create') }}" class="btn btn-primary w-full">
                        <i class="fas fa-plus mr-2"></i> Nouvelle performance
                    </a>
                    <a href="{{ route('defis.create') }}" class="btn btn-outline w-full">
                        <i class="fas fa-trophy mr-2"></i> Créer un défi
                    </a>
                    <a href="{{ route('posts.create') }}" class="btn btn-outline w-full">
                        <i class="fas fa-edit mr-2"></i> Écrire un post
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>

@push('scripts')
<script>
// Graphiques et interactions JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Code pour les graphiques Chart.js
    // Code pour les notifications en temps réel
    // Code pour les mises à jour AJAX
});
</script>
@endpush