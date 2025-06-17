<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /** Display a listing of the posts. */
    public function index()
    {
        // 1️⃣ On crée bien la variable $posts AVANT d’appeler view()
        $posts = Post::with('category')
                     ->orderBy('created_at', 'desc')
                     ->paginate(10);

        // 2️⃣ Puis on la passe à la vue
        return view('posts.index', compact('posts'));
    }

    /** Show the form for creating a new post. */
    public function create()
    {
        $categories = Category::all();
        return view('posts.create', compact('categories'));
    }

    /** Store a newly created post in storage. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Post::create($data);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Article créé avec succès.');
    }

    /** Display the specified post. */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /** Show the form for editing the specified post. */
    public function edit(Post $post)
    {
        $categories = Category::all();
        return view('posts.edit', [
            'post'       => $post,
            'categories' => $categories,
        ]);    
    }

    /** Update the specified post in storage. */
    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($data);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Article mis à jour.');
    }

    /** Remove the specified post from storage. */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Article supprimé.');
    }
}