<?php

namespace App\Http\Controllers;
use App\Models\Exercice;

use Illuminate\Http\Request;

class ExerciceController extends Controller
{
    public function index()
    {
        $exercices = Exercice::all();
        return view('exercices.index', compact('exercices'));
    }

    public function create()
    {
        return view('exercices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'niveau' => 'required|string|max:255',
        ]);

        Exercice::create($request->all());

        return redirect()->route('exercices.index');
    }
}
