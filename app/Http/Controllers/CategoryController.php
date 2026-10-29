<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * Halaman ini untuk admin mengelola kategori. Semua user boleh lihat
     * daftar kategori (dropdown ticket) — tapi manajemen = admin.
     */
    public function index()
    {
        Gate::authorize('viewAny', Category::class);

        return view('categories.index', [
            'categories' => Category::withCount('tickets')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        Gate::authorize('create', Category::class);

        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     *
     * Slug di-generate otomatis dari name (bukan input user — mencegah
     * slug aneh/kosong).
     */
    public function store(StoreCategoryRequest $request)
    {
        Category::create([
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        Gate::authorize('update', $category);

        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified category in storage.
     *
     * Slug ikut digenerate ulang dari name baru supaya konsisten.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->name = $request->validated('name');
        $category->slug = Str::slug($request->validated('name'));
        $category->save();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category from storage.
     *
     * Kategori yang masih dipakai ticket TIDAK bisa dihapus (foreign key
     * constraint) — dicek dulu supaya user dapat pesan yang jelas, bukan
     * error database 500.
     */
    public function destroy(Request $request, Category $category)
    {
        Gate::authorize('delete', $category);

        if ($category->tickets()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih dipakai oleh '
                    .$category->tickets()->count().' ticket.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
