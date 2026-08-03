<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', Category::class);

        return redirect()->route('products.index', ['tab' => 'kategori']);
    }

    public function create()
    {
        $this->authorize('create', Category::class);

        return view('categories.create');
    }

    public function storeJson(Request $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:kategoris,nama'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'id' => $category->id,
            'nama' => $category->nama,
            'message' => 'Kategori berhasil ditambahkan.',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:kategoris,nama'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        Category::create($validated);

        return redirect()
            ->route('products.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategoris', 'nama')->ignore($category->id),
            ],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        return redirect()
            ->route('products.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            return redirect()
                ->route('products.index', ['tab' => 'kategori'])
                ->with('error', 'Kategori tidak bisa dihapus karena masih dipakai oleh produk.');
        }

        $category->delete();

        return redirect()
            ->route('products.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
