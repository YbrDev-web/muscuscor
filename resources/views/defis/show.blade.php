@extends('layouts.app')

@section('head')
  <link rel="stylesheet" href="{{ asset('css/defis-show.css') }}">
@endsection

@section('content')
<div id="defi-show-page">
  <h1>{{ $defi->nom }}</h1>
  <p>Type : {{ $defi->type }}</p>
  <p>Niveau de difficulté : {{ $defi->niveau_difficulte }}</p>
  <form action="{{ route('defis.participer', $defi) }}" method="POST">
    @csrf
    <button type="submit">Participer</button>
  </form>
</div>
@endsection
