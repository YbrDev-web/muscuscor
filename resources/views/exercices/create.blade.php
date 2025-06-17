{{-- resources/views/exercices/create.blade.php --}}
@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/exercice-create.css') }}">
@endsection

@section('content')
<div id="exercice-create-page">
    <h1>Ajouter un exercice</h1>

    <form action="{{ route('exercices.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" name="nom" id="nom" required>
        </div>

        <div class="form-group">
            <label for="type">Type</label>
            <input type="text" name="type" id="type" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label for="niveau">Niveau</label>
            <input type="text" name="niveau" id="niveau" required>
        </div>

        <button type="submit">Ajouter</button>
    </form>
</div>
@endsection