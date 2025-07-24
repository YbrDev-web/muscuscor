{{-- resources/views/partials/menu.blade.php --}}
<nav class="bg-gray-800">
  <div class="container mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      <div class="flex items-center">
        <a href="{{ route('dashboard') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Accueil</a>
        <a href="{{ route('posts.index') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Articles</a>
        <a href="{{ route('categories.index') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Catégories</a>
        <a href="{{ route('crud.index') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">CRUD Personnalisé</a>
        <a href="{{ route('exercices.index') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Exercices</a>
        <a href="{{ route('defis.index') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Défis</a>
        <a href="{{ route('performances.index') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Performances</a>
      </div>
    </div>
  </div>
</nav>
