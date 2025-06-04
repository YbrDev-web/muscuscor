@extends('layouts.app')

@section('content')
    <h1>Exercices</h1>
    <a href="{{ route('exercices.create') }}">Ajouter un exercice</a>
    <ul>
        @foreach ($exercices as $exercice)
            <li>{{ $exercice->nom }} ({{ $exercice->type }})</li>
        @endforeach
    </ul>
@endsection
