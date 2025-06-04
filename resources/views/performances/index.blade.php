@extends('layouts.app')

@section('content')
    <h1>Mes Performances</h1>
    <a href="{{ route('performances.create') }}">Ajouter une performance</a>
    <ul>
        @foreach ($performances as $performance)
            <li>{{ $performance->exercice->nom }} : {{ $performance->poids }}kg pour {{ $performance->repetitions }} répétitions x {{ $performance->series }} séries</li>
        @endforeach
    </ul>
@endsection
