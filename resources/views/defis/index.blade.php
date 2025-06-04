@extends('layouts.app')

@section('content')
    <h1>Défis disponibles</h1>
    <a href="{{ route('defis.create') }}">Ajouter un défi</a>
    <ul>
        @foreach ($defis as $defi)
            <li><a href="{{ route('defis.show', $defi) }}">{{ $defi->nom }}</a> - Niveau : {{ $defi->niveau_difficulte }}</li>
        @endforeach
    </ul>
@endsection