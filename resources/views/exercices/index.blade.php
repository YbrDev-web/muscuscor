@extends('layouts.app')

@section('head')
  <link rel="stylesheet" href="{{ asset('css/exercice-index.css') }}">
@endsection

@section('content')
  <div id="exercice-index-page">
    <h1>Exercices</h1>
    <a href="{{ route('exercices.create') }}" class="add-btn">Ajouter un exercice</a>
    <ul>
      @foreach ($exercices as $exercice)
        <li>{{ $exercice->nom }} ({{ $exercice->type }})</li>
      @endforeach
    </ul>
  </div>
@endsection
