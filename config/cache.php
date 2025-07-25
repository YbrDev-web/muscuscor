<?php
// config/cache.php - Configuration du cache optimisée
return [
    'default' => env('CACHE_DRIVER', 'redis'),
    
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_connection' => null,
        ],
    ],
    
    'prefix' => env('CACHE_PREFIX', 'muscuscore_cache'),
];

// app/Http/Controllers/API/PerformanceApiController.php
namespace App\Http\Controllers\API;

use App\Models\{Performance, Exercice};
use App\Http\Resources\PerformanceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Cache};

class PerformanceApiController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = "user_performances_" . Auth::id() . "_" . md5(serialize($request->all()));
        
        $performances = Cache::remember($cacheKey, 300, function() use ($request) {
            $query = Auth::user()->performances()->with('exercice');
            
            if ($request->filled('exercice_id')) {
                $query->where('exercice_id', $request->exercice_id);
            }
            
            if ($request->filled('date_from')) {
                $query->where('date_performance', '>=', $request->date_from);
            }
            
            return $query->latest('date_performance')->paginate(20);
        });
        
        return PerformanceResource::collection($performances);
    }
    
    public function chartData(Exercice $exercice)
    {
        $cacheKey = "chart_data_" . Auth::id() . "_" . $exercice->id;
        
        $data = Cache::remember($cacheKey, 600, function() use ($exercice) {
            return Auth::user()->performances()
                ->where('exercice_id', $exercice->id)
                ->orderBy('date_performance')
                ->get(['date_performance', 'poids_max', 'repetitions_max', 'series'])
                ->map(function($perf) {
                    return [
                        'date' => $perf->date_performance->format('Y-m-d'),
                        'poids_max' => (float) $perf->poids_max,
                        'repetitions_max' => $perf->repetitions_max,
                        'volume_total' => $perf->volume_total,
                    ];
                });
        });
        
        return response()->json($data);
    }
}

// app/Http/Resources/PerformanceResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'exercice' => [
                'id' => $this->exercice->id,
                'nom' => $this->exercice->nom,
                'type' => $this->exercice->type,
            ],
            'date_performance' => $this->date_performance->format('Y-m-d'),
            'series' => $this->series,
            'poids_max' => (float) $this->poids_max,
            'repetitions_max' => $this->repetitions_max,
            'volume_total' => $this->volume_total,
            'duree_totale' => $this->duree_totale,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}

// app/Policies/PerformancePolicy.php
namespace App\Policies;

use App\Models\{User, Performance};

class PerformancePolicy
{
    public function view(User $user, Performance $performance)
    {
        return $user->id === $performance->user_id || 
               ($performance->user->profil_public && $user->isFollowing($performance->user));
    }
    
    public function update(User $user, Performance $performance)
    {
        return $user->id === $performance->user_id;
    }
    
    public function delete(User $user, Performance $performance)
    {
        return $user->id === $performance->user_id;
    }
}

// app/Policies/DefiPolicy.php
namespace App\Policies;

use App\Models\{User, Defi};

class DefiPolicy
{
    public function view(User $user, Defi $defi)
    {
        return $defi->statut === 'actif' || $user->id === $defi->createur_id;
    }
    
    public function update(User $user, Defi $defi)
    {
        return $user->id === $defi->createur_id && $defi->statut !== 'termine';
    }
    
    public function delete(User $user, Defi $defi)
    {
        return $user->id === $defi->createur_id && $defi->participants()->count() === 0;
    }
    
    public function participate(User $user, Defi $defi)
    {
        return $defi->statut === 'actif' && 
               !$defi->est_termine && 
               !$defi->participants()->where('user_id', $user->id)->exists() &&
               (!$defi->max_participants || $defi->participants()->count() < $defi->max_participants);
    }
}

// database/migrations/xxxx_create_notifications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

// database/migrations/xxxx_add_indexes_for_performance.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->index(['user_id', 'date_performance', 'exercice_id'], 'perf_user_date_exercice');
            $table->index(['exercice_id', 'poids_max'], 'perf_exercice_poids');
            $table->index(['date_performance'], 'perf_date');
        });
        
        Schema::table('defis', function (Blueprint $table) {
            $table->index(['statut', 'date_debut', 'date_fin'], 'defis_statut_dates');
            $table->index(['createur_id', 'statut'], 'defis_createur_statut');
        });
        
        Schema::table('posts', function (Blueprint $table) {
            $table->index(['user_id', 'is_published', 'created_at'], 'posts_user_published_date');
            $table->index(['category_id', 'is_published'], 'posts_category_published');
        });
    }
    
    public function down(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->dropIndex('perf_user_date_exercice');
            $table->dropIndex('perf_exercice_poids');
            $table->dropIndex('perf_date');
        });
        
        Schema::table('defis', function (Blueprint $table) {
            $table->dropIndex('defis_statut_dates');
            $table->dropIndex('defis_createur_statut');
        });
        
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_user_published_date');
            $table->dropIndex('posts_category_published');
        });
    }
};

// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use App\Models\Performance;
use App\Observers\PerformanceObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services personnalisés
        $this->app->singleton(\App\Services\PerformanceAnalytics::class);
        $this->app->singleton(\App\Services\BadgeService::class);
        $this->app->singleton(\App\Services\NotificationService::class);
    }
    
    public function boot(): void
    {
        // Configuration de performance pour Eloquent
        Model::preventLazyLoading(!app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
        
        // Enregistrement des observers
        Performance::observe(PerformanceObserver::class);
        
        // Configuration des vues
        view()->composer('*', function ($view) {
            $view->with('currentUser', auth()->user());
        });
        
        // Pagination personnalisée
        \Illuminate\Pagination\Paginator::defaultView('pagination.custom');
    }
}

// resources/js/app.js - JavaScript pour l'interactivité
import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
Alpine.start();

// Configuration globale des graphiques
Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;

// Composants Alpine.js pour l'interactivité
document.addEventListener('alpine:init', () => {
    Alpine.data('performanceChart', (exerciceId) => ({
        chart: null,
        loading: true,
        
        async init() {
            await this.loadChart();
        },
        
        async loadChart() {
            try {
                const response = await fetch(`/api/performances/chart-data/${exerciceId}`);
                const data = await response.json();
                
                const ctx = this.$refs.canvas.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.date),
                        datasets: [{
                            label: 'Poids max (kg)',
                            data: data.map(d => d.poids_max),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: true
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Erreur lors du chargement du graphique:', error);
            } finally {
                this.loading = false;
            }
        }
    }));
    
    Alpine.data('notificationCenter', () => ({
        notifications: [],
        unreadCount: 0,
        open: false,
        
        async init() {
            await this.loadNotifications();
            this.setupWebSocket();
        },
        
        async loadNotifications() {
            try {
                const response = await fetch('/api/notifications');
                const data = await response.json();
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
            } catch (error) {
                console.error('Erreur lors du chargement des notifications:', error);
            }
        },
        
        async markAsRead(notificationId) {
            try {
                await fetch(`/api/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification && !notification.read_at) {
                    notification.read_at = new Date().toISOString();
                    this.unreadCount--;
                }
            } catch (error) {
                console.error('Erreur lors du marquage comme lu:', error);
            }
        },
        
        setupWebSocket() {
            // Configuration WebSocket pour les notifications en temps réel
            if (window.Echo) {
                window.Echo.private(`App.Models.User.${window.currentUserId}`)
                    .notification((notification) => {
                        this.notifications.unshift(notification);
                        this.unreadCount++;
                        this.showToast(notification.title, notification.message);
                    });
            }
        },
        
        showToast(title, message) {
            // Affichage d'une notification toast
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50';
            toast.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-bell text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">${title}</p>
                        <p class="text-sm text-gray-500">${message}</p>
                    </div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    }));
    
    Alpine.data('exerciceSelector', () => ({
        exercices: [],
        selected: null,
        search: '',
        open: false,
        
        get filteredExercices() {
            if (!this.search) return this.exercices;
            return this.exercices.filter(ex => 
                ex.nom.toLowerCase().includes(this.search.toLowerCase()) ||
                ex.type.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        
        async init() {
            await this.loadExercices();
        },
        
        async loadExercices() {
            try {
                const response = await fetch('/api/exercices/search');
                this.exercices = await response.json();
            } catch (error) {
                console.error('Erreur lors du chargement des exercices:', error);
            }
        },
        
        selectExercice(exercice) {
            this.selected = exercice;
            this.open = false;
            this.$dispatch('exercice-selected', exercice);
        }
    }));
});

// Utilitaires JavaScript
window.MuscuScore = {
    // Formatage des nombres
    formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    },
    
    // Formatage des dates
    formatDate(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    },
    
    // Calcul du pourcentage de progression
    calculateProgress(current, target) {
        return Math.min(100, Math.round((current / target) * 100));
    },
    
    // Copie dans le presse-papiers
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showToast('Copié !', 'Le texte a été copié dans le presse-papiers');
        } catch (error) {
            console.error('Erreur lors de la copie:', error);
        }
    },
    
    // Affichage des toasts
    showToast(title, message, type = 'success') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-300 translate-x-full`;
        toast.innerHTML = `
            <div class="flex items-center">
                <strong class="mr-2">${title}</strong>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animation d'entrée
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Suppression automatique
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
};

// resources/css/app.css - Styles Tailwind personnalisés
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
    .btn {
        @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200;
    }
    
    .btn-primary {
        @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
    }
    
    .btn-secondary {
        @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
    }
    
    .btn-outline {
        @apply bg-white text-gray-700 border-gray-300 hover:bg-gray-50 focus:ring-blue-500;
    }
    
    .btn-sm {
        @apply px-3 py-1.5 text-xs;
    }
    
    .btn-lg {
        @apply px-6 py-3 text-base;
    }
    
    .nav-link {
        @apply text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200;
    }
    
    .nav-link.active {
        @apply text-blue-600 bg-blue-50;
    }
    
    .dropdown-item {
        @apply block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center;
    }
    
    .form-input {
        @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500;
    }
    
    .form-select {
        @apply block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
    }
    
    .form-textarea {
        @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 resize-vertical;
    }
    
    .status-badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    
    .status-actif {
        @apply bg-green-100 text-green-800;
    }
    
    .status-termine {
        @apply bg-gray-100 text-gray-800;
    }
    
    .status-brouillon {
        @apply bg-yellow-100 text-yellow-800;
    }
    
    .status-annule {
        @apply bg-red-100 text-red-800;
    }
    
    .card {
        @apply bg-white overflow-hidden shadow-sm rounded-lg;
    }
    
    .chart-container {
        @apply relative w-full h-64 md:h-80;
    }
    
    .notification-badge {
        @apply absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full;
    }
}

@layer utilities {
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .slide-up {
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    
    .pulse-slow {
        animation: pulse 3s infinite;
    }
}

// .env.example - Variables d'environnement
APP_NAME="MuscuScore"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=muscuscore
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=pusher
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@muscuscore.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

// composer.json - Dépendances recommandées
{
    "require": {
        "php": "^8.1",
        "guzzlehttp/guzzle": "^7.2",
        "laravel/framework": "^10.10",
        "laravel/sanctum": "^3.2",
        "laravel/tinker": "^2.8",
        "intervention/image": "^2.7",
        "spatie/laravel-backup": "^8.0",
        "spatie/laravel-permission": "^5.10",
        "pusher/pusher-php-server": "^7.0",
        "maatwebsite/excel": "^3.1"
    },
    "require-dev": {
        "fakerphp/faker": "^1.9.1",
        "laravel/pint": "^1.0",
        "laravel/sail": "^1.18",
        "mockery/mockery": "^1.4.4",
        "nunomaduro/collision": "^7.0",
        "phpunit/phpunit": "^10.1",
        "spatie/laravel-ignition": "^2.0",
        "barryvdh/laravel-debugbar": "^3.8"
    }
}

// Tests d'exemple - tests/Feature/PerformanceTest.php
namespace Tests\Feature;

use App\Models\{User, Exercice, Performance};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_create_performance()
    {
        $user = User::factory()->create();
        $exercice = Exercice::factory()->create();
        
        $response = $this->actingAs($user)->post('/performances', [
            'exercice_id' => $exercice->id,
            'date_performance' => now()->format('Y-m-d'),
            'series' => [
                ['poids' => 80, 'repetitions' => 10, 'repos' => 90],
                ['poids' => 80, 'repetitions' => 8, 'repos' => 90],
            ],
            'notes' => 'Bonne séance'
        ]);
        
        $response->assertRedirect('/performances');
        $this->assertDatabaseHas('performances', [
            'user_id' => $user->id,
            'exercice_id' => $exercice->id,
            'poids_max' => 80,
        ]);
    }
    
    public function test_user_cannot_view_private_performance()
    {
        $owner = User::factory()->create(['profil_public' => false]);
        $user = User::factory()->create();
        $performance = Performance::factory()->create(['user_id' => $owner->id]);
        
        $response = $this->actingAs($user)->get("/performances/{$performance->id}");
        
        $response->assertForbidden();
    }
}

// Documentation README.md
# 🏋️ MuscuScore - Réseau Social de Musculation

MuscuScore est une application Laravel moderne pour le suivi des performances de musculation, la création de défis communautaires et l'interaction sociale entre passionnés de fitness.

## 🚀 Fonctionnalités

### Core Features
- **Suivi de performances** : Enregistrement détaillé des séances avec séries, poids, répétitions
- **Système de défis** : Création et participation à des challenges communautaires
- **Réseau social** : Posts, likes, commentaires, système de suivi
- **Badges et récompenses** : Système de gamification avec achievements
- **Analytics** : Graphiques de progression et statistiques détaillées

### Fonctionnalités Avancées
- **Notifications temps réel** : WebSocket avec Pusher
- **API REST** : Points d'API pour applications mobiles
- **Cache intelligent** : Redis pour optimiser les performances
- **Jobs en arrière-plan** : Calcul des classements et digest
- **Sécurité renforcée** : Policies, CSRF, validation stricte

## 📋 Prérequis

- PHP 8.1+
- Composer
- Node.js 16+
- MySQL 8.0+ ou PostgreSQL
- Redis (recommandé)

## 🛠️ Installation

```bash
# Cloner le projet
git clone https://github.com/votre-username/muscuscore.git
cd muscuscore

# Installer les dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate --seed

# Assets
npm run build

# Lancer le serveur
php artisan serve
```

## 📈 Optimisations Implémentées

1. **Base de données** : Index optimisés, relations efficaces
2. **Cache** : Redis avec stratégies de cache intelligentes  
3. **Jobs** : Traitement asynchrone des tâches lourdes
4. **Frontend** : Alpine.js pour l'interactivité, Tailwind CSS
5. **Sécurité** : Policies, validation, protection CSRF

## 🔧 Configuration Production

```bash
# Optimisations Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue worker
php artisan queue:work --daemon

# Scheduler
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```