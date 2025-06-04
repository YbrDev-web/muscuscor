<?php


namespace App\Http\Controllers;

use App\Models\Defi;
use Illuminate\Http\Request;
use App\Notifications\DefiTermineNotification;

class DefiController extends Controller
{
    public function index()
    {
        $defis = Defi::all();
        return view('defis.index', compact('defis'));
    }

    public function create()
    {
        return view('defis.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'niveau de difficulté' => 'required|string|max:255',
            'Type de défi' => 'required|string|max:255',
        ]);

        Defi::create($request->all());

        return redirect()->route('defis.index');
    }

    public function show(Defi $defi)
    {
        return view('defis.show', compact('defi'));
    }

    public function participer(Defi $defi)
    {
        $defi->utilisateurs()->attach(auth()->id(), ['statut' => 'en cours']);
        return redirect()->route('defis.index');
    }

    
    
    public function terminerDefi(Defi $defi)
    {
        $user = auth()->user();
        // Marquer le défi comme terminé
        $user->defis()->updateExistingPivot($defi->id, ['statut' => 'complété']);
        // Envoi de la notification
        $user->notify(new DefiTermineNotification($defi));
        return redirect()->route('defis.index');
    }
}
