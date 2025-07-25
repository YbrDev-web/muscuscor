<?php
// app/Http/Middleware/CheckUserActivity.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            Auth::user()->update(['derniere_activite' => now()]);
        }
        
        return $next($request);
    }
}

// app/Http/Middleware/AdminMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Accès non autorisé');
        }
        
        return $next($request);
    }
}

// app/Services/PerformanceAnalytics.php
namespace App\Services;

use App\Models\{User, Performance, Exercice};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceAnalytics
{
    public function getUserProgressionData(User $user, string $period = '3months')
    {
        $startDate = $this->getStartDateForPeriod($period);
        
        return $user->performances()
            ->where('date_performance', '>=', $startDate)
            ->with('exercice')
            ->orderBy('date_performance')
            ->get()
            ->groupBy(function($performance) {
                return $performance->date_performance->format('Y-m-d');
            })
            ->map(function($performances) {
                return [
                    'date' => $performances->first()->date_performance->format('Y-m-d'),
                    'volume_total' => $performances->sum('volume_total'),
                    'nombre_exercices' => $performances->count(),
                    'poids_moyen' => $performances->avg('poids_max'),
                ];
            });
    }
    
    public function getExerciceProgression(User $user, Exercice $exercice, int $limit = 10)
    {
        return $user->performances()
            ->where('exercice_id', $exercice->id)
            ->orderBy('date_performance', 'desc')
            ->limit($limit)
            ->get(['date_performance', 'poids_max', 'repetitions_max', 'volume_total'])
            ->reverse()
            ->values();
    }
    
    public function calculateStrengthScore(User $user)
    {
        // Calcul d'un score de force basé sur les performances récentes
        $recentPerformances = $user->performances()
            ->where('date_performance', '>=', now()->subMonth())
            ->with('exercice')
            ->get();
            
        if ($recentPerformances->isEmpty()) {
            return 0;
        }
        
        $score = 0;
        $weights = [
            'force' => 1.5,
            'cardio' => 1.0,
            'flexibilite' => 0.8,
            'endurance' => 1.2
        ];
        
        foreach ($recentPerformances as $performance) {
            $exerciceWeight = $weights[$performance->exercice->type] ?? 1.0;
            $score += ($performance->volume_total * $exerciceWeight) / 100;
        }
        
        return round($score / $recentPerformances->count());
    }
    
    public function getWeeklyComparison(User $user)
    {
        $thisWeek = $this->getWeekStats($user, now()->startOfWeek(), now()->endOfWeek());
        $lastWeek = $this->getWeekStats($user, now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek());
        
        return [
            'this_week' => $thisWeek,
            'last_week' => $lastWeek,
            'comparison' => [
                'sessions' => $this->calculatePercentageChange($lastWeek['sessions'], $thisWeek['sessions']),
                'volume' => $this->calculatePercentageChange($lastWeek['volume'], $thisWeek['volume']),
                'duration' => $this->calculatePercentageChange($lastWeek['duration'], $thisWeek['duration']),
            ]
        ];
    }
    
    private function getWeekStats(User $user, $start, $end)
    {
        $performances = $user->performances()
            ->whereBetween('date_performance', [$start, $end])
            ->get();
            
        return [
            'sessions' => $performances->count(),
            'volume' => $performances->sum('volume_total'),
            'duration' => $performances->sum('duree_totale'),
            'exercices_uniques' => $performances->pluck('exercice_id')->unique()->count(),
        ];
    }
    
    private function calculatePercentageChange($old, $new)
    {
        if ($old == 0) return $new > 0 ? 100 : 0;
        return round((($new - $old) / $old) * 100, 1);
    }
    
    private function getStartDateForPeriod(string $period)
    {
        return match($period) {
            '1month' => now()->subMonth(),
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            '1year' => now()->subYear(),
            default => now()->subMonths(3),
        };
    }
}

// app/Services/BadgeService.php
namespace App\Services;

use App\Models\{User, Badge, Performance};

class BadgeService
{
    public function checkAndAwardBadges(User $user, Performance $performance = null)
    {
        $newBadges = [];
        
        // Badge première performance
        if ($user->performances()->count() === 1) {
            $newBadges[] = $this->awardBadge($user, 'premier_pas');
        }
        
        // Badge 10 performances
        if ($user->performances()->count() === 10) {
            $newBadges[] = $this->awardBadge($user, 'dedie');
        }
        
        // Badge 50 performances
        if ($user->performances()->count() === 50) {
            $newBadges[] = $this->awardBadge($user, 'athlete');
        }
        
        // Badge record personnel
        if ($performance && $this->isPersonalRecord($user, $performance)) {
            $newBadges[] = $this->awardBadge($user, 'record_personnel');
        }
        
        // Badge série de 7 jours consécutifs
        if ($this->hasSevenDayStreak($user)) {
            $newBadges[] = $this->awardBadge($user, 'serie_hebdo');
        }
        
        // Badge poids lourd (100kg+)
        if ($performance && $performance->poids_max >= 100) {
            $newBadges[] = $this->awardBadge($user, 'poids_lourd');
        }
        
        return array_filter($newBadges);
    }
    
    private function awardBadge(User $user, string $badgeSlug)
    {
        $badge = Badge::where('slug', $badgeSlug)->first();
        
        if ($badge && !$user->badges()->where('badge_id', $badge->id)->exists()) {
            $user->badges()->attach($badge->id, ['date_obtention' => now()]);
            return $badge;
        }
        
        return null;
    }
    
    private function isPersonalRecord(User $user, Performance $performance)
    {
        $previousBest = $user->performances()
            ->where('exercice_id', $performance->exercice_id)
            ->where('id', '!=', $performance->id)
            ->max('poids_max');
            
        return $performance->poids_max > $previousBest;
    }
    
    private function hasSevenDayStreak(User $user)
    {
        $dates = $user->performances()
            ->where('date_performance', '>=', now()->subDays(7))
            ->pluck('date_performance')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->unique()
            ->sort()
            ->values();
            
        if ($dates->count() < 7) return false;
        
        // Vérifier la continuité
        for ($i = 1; $i < $dates->count(); $i++) {
            $current = Carbon::parse($dates[$i]);
            $previous = Carbon::parse($dates[$i-1]);
            
            if ($current->diffInDays($previous) !== 1) {
                return false;
            }
        }
        
        return true;
    }
}

// app/Services/NotificationService.php
namespace App\Services;

use App\Models\{User, Notification};
use App\Events\{NewFollower, DefiCompleted, PersonalRecord};

class NotificationService
{
    public function notifyNewFollower(User $follower, User $following)
    {
        $notification = Notification::create([
            'user_id' => $following->id,
            'type' => 'new_follower',
            'title' => 'Nouvel abonné',
            'message' => "{$follower->name} a commencé à vous suivre",
            'data' => json_encode(['follower_id' => $follower->id]),
        ]);
        
        broadcast(new NewFollower($notification));
    }
    
    public function notifyDefiCompleted(User $user, $defi)
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'defi_completed',
            'title' => 'Défi terminé !',
            'message' => "Félicitations ! Vous avez terminé le défi : {$defi->titre}",
            'data' => json_encode(['defi_id' => $defi->id]),
        ]);
        
        broadcast(new DefiCompleted($notification));
    }
    
    public function notifyPersonalRecord(User $user, $performance)
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'personal_record',
            'title' => 'Record personnel !',
            'message' => "Nouveau record sur {$performance->exercice->nom} : {$performance->poids_max}kg",
            'data' => json_encode(['performance_id' => $performance->id]),
        ]);
        
        broadcast(new PersonalRecord($notification));
    }
    
    public function markAsRead(User $user, $notificationId = null)
    {
        $query = $user->notifications();
        
        if ($notificationId) {
            $query->where('id', $notificationId);
        }
        
        return $query->update(['read_at' => now()]);
    }
}

// app/Observers/PerformanceObserver.php
namespace App\Observers;

use App\Models\Performance;
use App\Services\{BadgeService, NotificationService};

class PerformanceObserver
{
    public function __construct(
        private BadgeService $badgeService,
        private NotificationService $notificationService
    ) {}
    
    public function created(Performance $performance)
    {
        // Vérifier et attribuer des badges
        $newBadges = $this->badgeService->checkAndAwardBadges($performance->user, $performance);
        
        // Notifier les nouveaux badges
        foreach ($newBadges as $badge) {
            // Envoyer notification badge obtenu
        }
        
        // Vérifier si c'est un record personnel
        if ($this->isPersonalRecord($performance)) {
            $this->notificationService->notifyPersonalRecord($performance->user, $performance);
        }
        
        // Mettre à jour les statistiques utilisateur
        $this->updateUserStats($performance->user);
    }
    
    private function isPersonalRecord(Performance $performance)
    {
        $previousBest = Performance::where('user_id', $performance->user_id)
            ->where('exercice_id', $performance->exercice_id)
            ->where('id', '!=', $performance->id)
            ->max('poids_max');
            
        return $performance->poids_max > $previousBest;
    }
    
    private function updateUserStats(User $user)
    {
        // Mettre à jour des statistiques en cache ou dans la DB
        cache()->forget("user_stats_{$user->id}");
    }
}

// app/Jobs/CalculateLeaderboards.php
namespace App\Jobs;

use App\Models\{User, Defi};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class CalculateLeaderboards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        // Calculer le classement mensuel
        $this->calculateMonthlyLeaderboard();
        
        // Mettre à jour les classements des défis
        $this->updateChallengeLeaderboards();
        
        // Calculer les scores de force
        $this->calculateStrengthScores();
    }
    
    private function calculateMonthlyLeaderboard()
    {
        $users = User::with('performances')
            ->whereHas('performances', function($query) {
                $query->whereBetween('date_performance', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]);
            })
            ->get();
            
        $rankings = $users->map(function($user) {
            $monthlyPerformances = $user->performances()
                ->whereBetween('date_performance', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])
                ->get();
                
            return [
                'user_id' => $user->id,
                'total_volume' => $monthlyPerformances->sum('volume_total'),
                'total_sessions' => $monthlyPerformances->count(),
                'score' => $this->calculateUserScore($monthlyPerformances),
            ];
        })->sortByDesc('score')->values();
        
        // Sauvegarder en cache
        cache()->put('monthly_leaderboard', $rankings, now()->addHour());
    }
    
    private function updateChallengeLeaderboards()
    {
        $activeDefis = Defi::actif()->get();
        
        foreach ($activeDefis as $defi) {
            $participants = $defi->participants()
                ->withPivot('progression')
                ->get()
                ->sortByDesc('pivot.progression.valeur')
                ->take(10);
                
            cache()->put("defi_leaderboard_{$defi->id}", $participants, now()->addMinutes(30));
        }
    }
    
    private function calculateStrengthScores()
    {
        User::chunk(100, function($users) {
            foreach ($users as $user) {
                $score = app(PerformanceAnalytics::class)->calculateStrengthScore($user);
                $user->update(['strength_score' => $score]);
            }
        });
    }
    
    private function calculateUserScore($performances)
    {
        $volumeScore = $performances->sum('volume_total') / 1000; // Points pour volume
        $consistencyScore = $performances->count() * 10; // Points pour régularité
        $varietyScore = $performances->pluck('exercice_id')->unique()->count() * 5; // Points pour variété
        
        return $volumeScore + $consistencyScore + $varietyScore;
    }
}

// app/Console/Commands/SendWeeklyDigest.php
namespace App\Console\Commands;

use App\Models\User;
use App\Mail\WeeklyDigest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    protected $signature = 'digest:weekly';
    protected $description = 'Envoie le digest hebdomadaire aux utilisateurs actifs';
    
    public function handle()
    {
        $activeUsers = User::active()
            ->where('receive_digest', true)
            ->get();
            
        $this->info("Envoi du digest à {$activeUsers->count()} utilisateurs...");
        
        foreach ($activeUsers as $user) {
            try {
                Mail::to($user)->send(new WeeklyDigest($user));
                $this->info("Digest envoyé à {$user->email}");
            } catch (\Exception $e) {
                $this->error("Erreur pour {$user->email}: {$e->getMessage()}");
            }
        }
        
        $this->info('Envoi terminé !');
    }
}

// app/Mail/WeeklyDigest.php
namespace App\Mail;

use App\Models\User;
use App\Services\PerformanceAnalytics;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyDigest extends Mailable
{
    use Queueable, SerializesModels;
    
    public function __construct(
        private User $user
    ) {}
    
    public function build()
    {
        $analytics = app(PerformanceAnalytics::class);
        $weeklyStats = $analytics->getWeeklyComparison($this->user);
        
        // Données pour l'email
        $data = [
            'user' => $this->user,
            'stats' => $weeklyStats,
            'recent_achievements' => $this->user->badges()
                ->wherePivot('date_obtention', '>=', now()->subWeek())
                ->get(),
            'suggested_challenges' => $this->getSuggestedChallenges(),
        ];
        
        return $this->subject('Votre résumé hebdomadaire MuscuScore')
                    ->view('emails.weekly-digest', $data);
    }
    
    private function getSuggestedChallenges()
    {
        return Defi::disponible()
            ->whereNotIn('id', $this->user->defisParticipated()->pluck('defi_id'))
            ->limit(3)
            ->get();
    }
}

// config/queue.php - Configuration des queues
return [
    'default' => env('QUEUE_CONNECTION', 'database'),
    
    'connections' => [
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],
        
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],
    
    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],
];

// app/Console/Kernel.php - Planification des tâches
protected function schedule(Schedule $schedule)
{
    // Calcul des classements quotidien
    $schedule->job(CalculateLeaderboards::class)->daily();
    
    // Envoi du digest hebdomadaire le dimanche
    $schedule->command('digest:weekly')
             ->weeklyOn(0, '08:00');
    
    // Nettoyage des notifications anciennes
    $schedule->command('notifications:cleanup')
             ->weekly();
    
    // Sauvegarde de la base de données
    $schedule->command('backup:run')
             ->daily()
             ->at('02:00');
             
    // Mise à jour des statistiques utilisateurs
    $schedule->job(UpdateUserStats::class)
             ->hourly();
}