@extends('layouts.app')

@section('head')
  <link rel="stylesheet" href="{{ asset('css/defis-create.css') }}">
@endsection

@section('content')
<div id="defi-create-page">
  <h1>Ajouter un défi</h1>

  <form action="{{ route('defis.store') }}" method="POST">
    @csrf

    <div>
      <label for="nom">Nom du défi</label>
      <input type="text" name="nom" id="nom" required>
    </div>

    <div>
      <label for="niveau_difficulte">Niveau de difficulté</label>
      <input type="text" name="niveau_difficulte" id="niveau_difficulte" required>
    </div>

    <div>
      <label for="type">Type de défi</label>
      <input type="text" name="type" id="type" required>
    </div>

    <button type="submit">Ajouter</button>
  </form>
</div>

@endsection
