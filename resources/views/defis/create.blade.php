@extends('layouts.app')

@section('content')
    <h1>Ajouter un défi</h1>

    <form action="{{ route('defis.store') }}" method="POST">
        @csrf
        <div>
            <label for="nom">Nom du défi</label>
            <input type="text" name="nom" id="nom" required>
        </div>

        <div>
            <label for="niveau de difficulté">Niveau de difficulté</label>
            <input type="text" name="niveau de difficulté" id="niveau de difficulté" required>
        </div>

        <div>
            <label for="type">Type de défi</label>
            <input type="text" name="type" id="type" required>
        </div>

        <button type="submit">Ajouter</button>
    </form>
@endsection
