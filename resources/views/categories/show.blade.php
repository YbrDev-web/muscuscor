{{-- resources/views/categories/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">Catégorie : {{ $category->name }}</h1>

        @if($category->posts->count())
            <h2 class="text-xl font-semibold mb-2 text-gray-700 dark:text-gray-200">Articles dans cette catégorie :</h2>
            <ul class="list-disc list-inside space-y-2">
                @foreach($category->posts as $post)
                    <li>
                        <a href="{{ route('posts.show', $post) }}" class="text-blue-600 hover:underline dark:text-blue-400">
                            {{ $post->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-600 dark:text-gray-400">Aucun article pour cette catégorie.</p>
        @endif

        <div class="mt-6 flex space-x-4">
            <a href="{{ route('categories.edit', $category) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                Modifier
            </a>
            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Supprimer
                </button>
            </form>
            <a href="{{ route('categories.index') }}" class="ml-auto text-blue-600 hover:underline dark:text-blue-400">
                ← Retour à la liste
            </a>
        </div>
    </div>
</div>
@endsection
