{{-- resources/views/posts/index.blade.php --}}
@extends('layouts.app')

@section('head')
  <link rel="stylesheet" href="{{ asset('css/posts-cards.css') }}">
@endsection

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-white">Liste des articles</h1>

        {{-- Seuls les users ayant la permission "publier articles" peuvent créer --}}
        @can('publier articles')
        <a href="{{ route('posts.create') }}"
           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
           + Nouvel article
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($posts->count())
        <div class="cards-table">
            @foreach($posts as $post)
                <div class="post-card p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2">
                        {{ $post->title }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Catégorie : {{ $post->category->name ?? '-' }}
                    </p>
                    @role('admin')
                        <strong><p class="text-sm text-gray-500" style="font-size: large;">Auteur : {{ $post->user->name }}</p></strong>
                    @endrole
                    <div class="flex space-x-2">
                        {{-- Tout le monde peut voir --}}
                        <a href="{{ route('posts.show', $post) }}" class="text-blue-600 hover:underline">
                            Voir
                        </a>

                        {{-- Modifier : permission "modifier articles" requise --}}
                        @can('modifier articles')
                        <a href="{{ route('posts.edit', $post) }}" class="text-yellow-600 hover:underline">
                            Modifier
                        </a>
                        @endcan

                        {{-- Supprimer : permission "supprimer articles" requise --}}
                        @can('supprimer articles')
                        <form action="{{ route('posts.destroy', $post) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Supprimer cet article ?')"
                                    class="text-red-600 hover:underline">
                                Supprimer
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    @else
        <p class="text-gray-600">Aucun article trouvé.</p>
    @endif
</div>
@endsection
