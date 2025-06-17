@extends('layouts.app')

@section('head')
  <link rel="stylesheet" href="{{ asset('css/defis-index.css') }}">
@endsection

@section('content')
  <div id="defis-index-page">
    <h1>Défis disponibles</h1>
    <a href="{{ route('defis.create') }}" class="add-defi-btn">Ajouter un défi</a>
    <ul>
      @foreach ($defis as $defi)
        <li>
          <a href="{{ route('defis.show', $defi) }}">{{ $defi->nom }}</a>
          <span class="niveau">Niveau : {{ $defi->niveau_difficulte }}</span>
        </li>
      @endforeach
    </ul>
  </div>
@endsection
