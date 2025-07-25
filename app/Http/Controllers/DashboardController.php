<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\{User, Performance, Defi, Post};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Statistiques personnelles
        $stats = [
            'performances_semaine' => $user->performances()
                ->whereBetween('date_performance', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'defis_actifs' => $user->defisParticipated()
                ->wherePivot('objectif_atteint', false)
                ->count(),
            'badges_total' => $user->badges()->count(),
            'followers_count' => $user->followers()->count(),
        ];
        
        // Dernières performances
        $recentPerformances = $user->performances()
            ->with('exercice')
            ->latest('date_performance')
            ->limit(5)
            ->get();
        
        // Défis recommandés
        $defisRecommandes = Defi::disponible()
            ->whereNotIn('id', $user->defisParticipated()->pluck('defi_id'))
            ->where('niveau', $user->niveau)
            ->limit(3)
            ->get();
        
        // Feed d'actualités
        $feedPosts = Post::published()
            ->whereIn('user_id', $user->following()->pluck('users.id')->merge([$user->id]))
            ->with(['user', 'likes', 'commentaires'])
            ->latest()
            ->limit(10)
            ->get();
        
        return view('dashboard', compact('stats', 'recentPerformances', 'defisRecommandes', 'feedPosts'));
    }
}

// app/Http/Controllers/PerformanceController.php
namespace App\Http\Controllers;

use App\Models\{Performance, Exercice};
use App\Http\Requests\PerformanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->performances()->with('exercice');
        
        // Filtres
        if ($request->filled('exercice_id')) {
            $query->where('exercice_id', $request->exercice_id);
        }
        
        if ($request->filled('date_debut')) {
            $query->where('date_performance', '>=', $request->date_debut);
        }
        
        if ($request->filled('date_fin')) {
            $query->where('date_performance', '<=', $request->date_fin);
        }
        
        $performances = $query->latest('date_performance')->paginate(15);
        $exercices = Exercice::active()->orderBy('nom')->get();
        
        return view('performances.index', compact('performances', 'exercices'));
    }
    
    public function create()
    {
        $exercices = Exercice::active()->orderBy('nom')->get();
        return view('performances.create', compact('exercices'));
    }
    
    public function store(PerformanceRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        
        // Calculer automatiquement les maxima
        if (!empty($data['series'])) {
            $data['poids_max'] = collect($data['series'])->max('poids');
            $data['repetitions_max'] = collect($data['series'])->max('repetitions');
        }
        
        Performance::create($data);
        
        return redirect()->route('performances.index')
            ->with('success', 'Performance enregistrée avec succès !');
    }
    
    public function show(Performance $performance)
    {
        $this->authorize('view', $performance);
        
        $performance->load('exercice', 'user');
        
        // Performances précédentes pour comparaison
        $previousPerformances = Performance::where('user_id', $performance->user_id)
            ->where('exercice_id', $performance->exercice_id)
            ->where('date_performance', '<', $performance->date_performance)
            ->latest('date_performance')
            ->limit(5)
            ->get();
        
        return view('performances.show', compact('performance', 'previousPerformances'));
    }
    
    public function statsGeneral()
    {
        $user = Auth::user();
        
        // Statistiques générales
        $stats = [
            'total_sessions' => $user->performances()->count(),
            'total_volume' => $user->performances()->get()->sum('volume_total'),
            'exercices_pratiques' => $user->performances()->distinct('exercice_id')->count(),
            'progression_hebdo' => $this->calculateWeeklyProgression($user),
        ];
        
        // Données pour graphiques
        $chartData = [
            'evolution_volume' => $this->getVolumeEvolution($user),
            'repartition_exercices' => $this->getExercicesRepartition($user),
        ];
        
        return view('performances.stats-general', compact('stats', 'chartData'));
    }
    
    public function chartData(Exercice $exercice)
    {
        $performances = Auth::user()->performances()
            ->where('exercice_id', $exercice->id)
            ->orderBy('date_performance')
            ->get(['date_performance', 'poids_max', 'repetitions_max', 'series']);
        
        $data = $performances->map(function ($perf) {
            return [
                'date' => $perf->date_performance->format('Y-m-d'),
                'poids_max' => $perf->poids_max,
                'repetitions_max' => $perf->repetitions_max,
                'volume_total' => $perf->volume_total,
                'nombre_series' => $perf->nombre_series,
            ];
        });
        
        return response()->json($data);
    }
    
    private function calculateWeeklyProgression($user)
    {
        $thisWeek = $user->performances()
            ->whereBetween('date_performance', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
            
        $lastWeek = $user->performances()
            ->whereBetween('date_performance', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->count();
            
        return $lastWeek > 0 ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1) : 0;
    }
    
    private function getVolumeEvolution($user)
    {
        return $user->performances()
            ->selectRaw('DATE(date_performance) as date, SUM(JSON_EXTRACT(series, "$[*].poids") * JSON_EXTRACT(series, "$[*].repetitions")) as volume')
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get();
    }
    
    private function getExercicesRepartition($user)
    {
        return $user->performances()
            ->join('exercices', 'performances.exercice_id', '=', 'exercices.id')
            ->selectRaw('exercices.nom, COUNT(*) as count')
            ->groupBy('exercices.id', 'exercices.nom')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }
}

// app/Http/Controllers/DefiController.php
namespace App\Http\Controllers;

use App\Models\{Defi, Exercice, User};
use App\Http\Requests\DefiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DefiController extends Controller
{
    public function index(Request $request)
    {
        $query = Defi::with(['createur', 'exercice', 'participants']);
        
        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('ma_participation')) {
            $query->whereHas('participants', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }
        
        $defis = $query->latest()->paginate(12);
        
        return view('defis.index', compact('defis'));
    }
    
    public function create()
    {
        $exercices = Exercice::active()->orderBy('nom')->get();
        return view('defis.create', compact('exercices'));
    }
    
    public function store(DefiRequest $request)
    {
        $data = $request->validated();
        $data['createur_id'] = Auth::id();
        
        $defi = Defi::create($data);
        
        return redirect()->route('defis.show', $defi)
            ->with('success', 'Défi créé avec succès !');
    }
    
    public function show(Defi $defi)
    {
        $defi->load(['createur', 'exercice', 'participants.user']);
        
        $userParticipation = null;
        if (Auth::check()) {
            $userParticipation = $defi->participants()
                ->where('user_id', Auth::id())
                ->first();
        }
        
        // Classement des participants
        $leaderboard = $defi->participants()
            ->orderByPivot('progression->valeur', 'desc')
            ->limit(10)
            ->get();
        
        return view('defis.show', compact('defi', 'userParticipation', 'leaderboard'));
    }
    
    public function participer(Defi $defi)
    {
        $user = Auth::user();
        
        // Vérifications
        if ($defi->est_termine) {
            return back()->with('error', 'Ce défi est terminé.');
        }
        
        if ($defi->participants()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Vous participez déjà à ce défi.');
        }
        
        if ($defi->max_participants && $defi->participants()->count() >= $defi->max_participants) {
            return back()->with('error', 'Ce défi est complet.');
        }
        
        $defi->participants()->attach($user->id, [
            'date_inscription' => now(),
            'progression' => ['valeur' => 0, 'unite' => $defi->objectif['unite'] ?? null],
        ]);
        
        return back()->with('success', 'Vous participez maintenant à ce défi !');
    }
    
    public function updateProgression(Request $request, Defi $defi)
    {
        $request->validate([
            'valeur' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $user = Auth::user();
        $participation = $defi->participants()->where('user_id', $user->id)->first();
        
        if (!$participation) {
            return back()->with('error', 'Vous ne participez pas à ce défi.');
        }
        
        $progression = [
            'valeur' => $request->valeur,
            'unite' => $defi->objectif['unite'] ?? null,
            'notes' => $request->notes,
            'derniere_maj' => now(),
        ];
        
        // Vérifier si l'objectif est atteint
        $objectifAtteint = $request->valeur >= $defi->objectif['valeur'];
        
        $defi->participants()->updateExistingPivot($user->id, [
            'progression' => $progression,
            'objectif_atteint' => $objectifAtteint,
            'date_completion' => $objectifAtteint ? now() : null,
        ]);
        
        $message = $objectifAtteint ? 'Félicitations ! Objectif atteint !' : 'Progression mise à jour.';
        
        return back()->with('success', $message);
    }
    
    public function leaderboard(Defi $defi)
    {
        $participants = $defi->participants()
            ->withPivot('progression', 'objectif_atteint', 'date_completion')
            ->orderByPivot('progression->valeur', 'desc')
            ->get();
        
        return response()->json($participants);
    }
}

// app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Models\{Post, Category};
use App\Http\Requests\PostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::published()->with(['user', 'category', 'likes']);
        
        // Filtres
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('contenu', 'like', "%{$search}%");
            });
        }
        
        $posts = $query->latest()->paginate(10);
        $categories = Category::orderBy('nom')->get();
        
        return view('posts.index', compact('posts', 'categories'));
    }
    
    public function create()
    {
        $categories = Category::orderBy('nom')->get();
        return view('posts.create', compact('categories'));
    }
    
    public function store(PostRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['slug'] = Str::slug($data['titre']);
        
        // Gestion de l'upload d'image
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }
        
        Post::create($data);
        
        return redirect()->route('posts.index')
            ->with('success', 'Article publié avec succès !');
    }
    
    public function show(Post $post)
    {
        $post->load(['user', 'category', 'likes', 'commentaires.user']);
        
        // Articles similaires
        $articlesSimiliares = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->limit(3)
            ->get();
        
        return view('posts.show', compact('post', 'articlesSimiliares'));
    }
    
    public function feed()
    {
        $user = Auth::user();
        
        // Posts des utilisateurs suivis + propres posts
        $posts = Post::published()
            ->whereIn('user_id', $user->following()->pluck('users.id')->merge([$user->id]))
            ->with(['user', 'category', 'likes', 'commentaires'])
            ->latest()
            ->paginate(15);
        
        return view('posts.feed', compact('posts'));
    }
}

// app/Http/Requests/PerformanceRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerformanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'exercice_id' => 'required|exists:exercices,id',
            'date_performance' => 'required|date|before_or_equal:today',
            'series' => 'required|array|min:1',
            'series.*.poids' => 'nullable|numeric|min:0|max:1000',
            'series.*.repetitions' => 'required|integer|min:1|max:1000',
            'series.*.repos' => 'nullable|integer|min:0|max:600',
            'duree_totale' => 'nullable|integer|min:1|max:28800',
            'notes' => 'nullable|string|max:1000',
        ];
    }
    
    public function messages(): array
    {
        return [
            'exercice_id.required' => 'Veuillez sélectionner un exercice.',
            'exercice_id.exists' => 'L\'exercice sélectionné n\'existe pas.',
            'date_performance.required' => 'La date est obligatoire.',
            'date_performance.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'series.required' => 'Au moins une série est requise.',
            'series.*.repetitions.required' => 'Le nombre de répétitions est obligatoire.',
            'series.*.repetitions.min' => 'Au moins 1 répétition est requise.',
        ];
    }
}

// app/Http/Requests/DefiRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DefiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'titre' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'type' => 'required|in:poids,repetitions,duree,frequence',
            'objectif' => 'required|array',
            'objectif.valeur' => 'required|numeric|min:1',
            'objectif.unite' => 'required|string|max:20',
            'exercice_id' => 'nullable|exists:exercices,id',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after:date_debut',
            'max_participants' => 'nullable|integer|min:2|max:1000',
            'regles' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}