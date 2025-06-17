@extends('layouts.app')

@section('head')
  <link rel="stylesheet" href="{{ asset('css/categories-create.css') }}">
@endsection

@section('content')
<div id="category-create-page">
  <h1>Créer une nouvelle catégorie</h1>

  @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('categories.store') }}" method="POST">
    @csrf

    <div>
      <label for="name">Nom de la catégorie</label>
      <input type="text" name="name" id="name" value="{{ old('name') }}" required>
    </div>

    <button type="submit">Enregistrer</button>
  </form>
</div>
@endsection
