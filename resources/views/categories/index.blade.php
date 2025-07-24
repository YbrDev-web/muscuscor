@extends('layouts.app')

@section('head')
  <link rel="stylesheet" href="{{ asset('css/categories-index.css') }}">
@endsection

@section('content')
<div id="categories-index-page">
    <h1>Liste des catégories</h1>
    @can('publier articles')
    <a href="{{ route('categories.create') }}" class="add-category-btn">
        + Nouvelle catégorie
    </a>
    @endcan

    @if(session('success'))
        <div class="bg-green-100">
            {{ session('success') }}
        </div>
    @endif

    @if($categories->count())
        <ul>
            @foreach($categories as $category)
                <li>
                    <a href="{{ route('categories.show', $category) }}">{{ $category->name }}</a>
                    <div class="actions">
                        @can('modifier articles')
                            <a href="{{ route('categories.edit', $category) }}">Modifier</a>
                        @endcan
                        @can('supprimer articles')
                            <form action="{{ route('categories.destroy', $category) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Supprimer cette catégorie ?')">
                                    Supprimer
                                </button>
                            </form>
                        @endcan

                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    @else
        <p>Aucune catégorie trouvée.</p>
    @endif
</div>
@endsection
