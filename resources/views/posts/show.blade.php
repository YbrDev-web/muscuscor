{{-- resources/views/posts/show.blade.php --}}
@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<div class="container mx-auto p-4 post-show">
    <h1 class="text-2xl font-bold mb-2">{{ $post->title }}</h1>
    <p class="text-gray-600 mb-4">Catégorie : {{ $post->category->name ?? '—' }}</p>
    <div class="prose">
        {!! nl2br(e($post->content)) !!}
    </div>
    @role('admin')
        <strong><p class="text-sm text-gray-500" style="font-size: large;">Auteur : {{ $post->user->name }}</p></strong>
    @endrole


    <div class="mt-6 flex space-x-2">
        {{-- Tout le monde peut voir --}}
        <a href="{{ route('posts.index') }}" class="inline-block text-blue-600 hover:underline">
            ← Retour à la liste
        </a>

        {{-- Modifier : uniquement si l’utilisateur a la permission --}}
        @can('modifier articles')
            <a href="{{ route('posts.edit', $post) }}"
               class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                Modifier
            </a>
        @endcan

        {{-- Supprimer : uniquement si l’utilisateur a la permission --}}
        @can('supprimer articles')
            <form action="{{ route('posts.destroy', $post) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Supprimer cet article ?')"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Supprimer
                </button>
            </form>
        @endcan
    </div>
</div>
@endsection
