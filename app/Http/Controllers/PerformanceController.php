<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Models\Exercice;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index()
    {
        $performances = Performance::where('user_id', auth()->id())->get();
        return view('performances.index', compact('performances'));
    }

    public function create()
    {
        $exercices = Exercice::all();
        return view('performances.create', compact('exercices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'exercice_id' => 'required|exists:exercices,id',
            'poids' => 'required|numeric',
            'repetitions' => 'required|numeric',
            'series' => 'required|numeric',
        ]);

        Performance::create([
            'user_id' => auth()->id(),
            'exercice_id' => $request->exercice_id,
            'poids' => $request->poids,
            'repetitions' => $request->repetitions,
            'series' => $request->series,
        ]);

        return redirect()->route('performances.index');
    }
}
