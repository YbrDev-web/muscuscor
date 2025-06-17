<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /** Display a listing of the categories. */
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(10);
        return view('categories.index', compact('categories'));
    }

    /** Show the form for creating a new category. */
    public function create()
    {
        return view('categories.create');
    }

    /** Store a newly created category in storage. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /** Display the specified category. */
    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    /** Show the form for editing the specified category. */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /** Update the specified category in storage. */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    /** Remove the specified category from storage. */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie supprimée.');
    }
}
