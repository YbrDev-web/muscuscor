@extends('layouts.app')

@section('content')
    <h1>Ajouter un exercice</h1>

    <form action="{{ route('exercices.store') }}" method="POST">
        @csrf
        <div>
            <label for="nom">Nom</label>
            <input type="text" name="nom" id="nom" required>
        </div>

        <div>
            <label for="type">Type</label>
            <input type="text" name="type" id="type" required>
        </div>

        <div>
            <label for="type">Description</label>
            <input type="text" name="description" id="description" required>
        </div>

        <div>
            <label for="niveau">Niveau</label>
            <input type="text" name="niveau" id="niveau" required>
        </div>

        <button type="submit">Ajouter</button>
    </form>
@endsection
