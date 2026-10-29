<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->toString());

        $categories = Category::query()
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image_url' => $category->image ? Storage::disk('public')->url($category->image) : null,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'products_count' => $category->products_count,
            ]);

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/categories/Create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $storedImage = $request->file('image')?->store('categories', 'public');

        try {
            Category::query()->create([
                ...$data,
                'image' => $storedImage,
            ]);
        } catch (Throwable $exception) {
            if ($storedImage) {
                Storage::disk('public')->delete($storedImage);
            }

            throw $exception;
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Catégorie créée avec succès.']);

        return to_route('admin.categories.index');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('admin/categories/Edit', [
            'category' => [
                ...$category->only(['id', 'name', 'description', 'is_active', 'sort_order']),
                'image_url' => $category->image ? Storage::disk('public')->url($category->image) : null,
            ],
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $oldImage = $category->image;
        $newImage = $request->file('image')?->store('categories', 'public');

        if ($newImage) {
            $data['image'] = $newImage;
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        try {
            $category->update($data);
        } catch (Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($oldImage && ($newImage || $request->boolean('remove_image'))) {
            Storage::disk('public')->delete($oldImage);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Catégorie mise à jour.']);

        return to_route('admin.categories.index');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->withTrashed()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Cette catégorie est utilisée par des produits et ne peut pas être supprimée.',
            ]);

            return back();
        }

        $image = $category->image;
        $category->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Catégorie supprimée.']);

        return to_route('admin.categories.index');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Statut de la catégorie mis à jour.']);

        return back();
    }
}
