@extends('layouts.app')

@section('content')
    <h1>Ajouter une performance</h1>

    <form action="{{ route('performances.store') }}" method="POST">
        @csrf
        <div>
            <label for="exercice_id">Exercice</label>
            <select name="exercice_id" id="exercice_id" required>
                @foreach ($exercices as $exercice)
                    <option value="{{ $exercice->id }}">{{ $exercice->nom }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="poids">Poids soulevé (kg)</label>
            <input type="number" name="poids" id="poids" required>
        </div>

        <div>
            <label for="repetitions">Nombre de répétitions</label>
            <input type="number" name="repetitions" id="repetitions" required>
        </div>

        <div>
            <label for="series">Nombre de séries</label>
            <input type="number" name="series" id="series" required>
        </div>

        <button type="submit">Enregistrer</button>
    </form>
@endsection
