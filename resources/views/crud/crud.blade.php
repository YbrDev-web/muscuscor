{{-- resources/views/posts/create.blade.php --}}
@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/posts-create.css') }}">
@endsection

@section('content')
<div id="post-create-page">
    <h1>Créer un nouvel article</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('posts.store') }}" method="POST">
        @csrf

        <div>
            <label for="title">Titre</label>
            <input
                type="text"
                name="title"
                id="title"
                value="{{ old('title') }}"
                required
            >
        </div>

        <div>
            <label for="content">Contenu</label>
            <textarea
                name="content"
                id="content"
                rows="5"
                required
            >{{ old('content') }}</textarea>
        </div>

        <div>
            <label for="category_id">Catégorie</label>
            <select name="category_id" id="category_id">
                <option value="">-- Sélectionner --</option>
                @foreach(\App\Models\Category::all() as $category)
                    <option
                        value="{{ $category->id }}"
                        {{ old('category_id') == $category->id ? 'selected' : '' }}
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit">Enregistrer</button>
    </form>
</div>
@endsection
