{{-- resources/views/posts/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Modifier l'article</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('posts.update', $post) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="title" class="block font-medium">Titre</label>
            <input
                type="text"
                name="title"
                id="title"
                value="{{ old('title', $post->title) }}"
                class="w-full border rounded px-3 py-2"
            >
        </div>

        <div>
            <label for="content" class="block font-medium">Contenu</label>
            <textarea
                name="content"
                id="content"
                rows="5"
                class="w-full border rounded px-3 py-2"
            >{{ old('content', $post->content) }}</textarea>
        </div>

        <div>
            <label for="category_id" class="block font-medium">Catégorie</label>
            <select
                name="category_id"
                id="category_id"
                class="w-full border rounded px-3 py-2"
            >
                <option value="">-- Sélectionnez une catégorie --</option>
                @foreach($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <button
                type="submit"
                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
            >
                Mettre à jour
            </button>
            <a
                href="{{ route('posts.show', $post) }}"
                class="text-blue-600 hover:underline"
            >
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
