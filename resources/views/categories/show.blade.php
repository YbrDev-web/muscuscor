{{-- resources/views/categories/show.blade.php --}}
@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/categories-show.css') }}">
@endsection

@section('content')
<div id="category-show-page">
    <h1>Catégorie : {{ $category->name }}</h1>

    @if($category->posts->count())
        <h2>Articles dans cette catégorie :</h2>
        <ul>
            @foreach($category->posts as $post)
                <li>
                    <a href="{{ route('posts.show', $post) }}">
                        {{ $post->title }}
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p>Aucun article pour cette catégorie.</p>
    @endif

    <div class="actions">
        @can('modifier articles')
            <a href="{{ route('categories.edit', $category) }}" class="bg-yellow-500">
                Modifier
            </a>
        @endcan
        @can('supprimer articles')
            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500">
                    Supprimer
                </button>
            </form>
        @endcan
        <a href="{{ route('categories.index') }}" class="ml-auto">
            ← Retour à la liste
        </a>
    </div>
</div>
@endsection
