@extends('layouts.app')

@section('content')
    <h1>{{ $defi->nom }}</h1>
    <p>Type : {{ $defi->type }}</p>
    <p>Niveau de difficulté : {{ $defi->niveau_difficulte }}</p>
    <form action="{{ route('defis.participer', $defi) }}" method="POST">
    @csrf
    <button type="submit">Participer</button>
</form>
@endsection
