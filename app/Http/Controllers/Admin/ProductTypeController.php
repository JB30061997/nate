<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductTypeRequest;
use App\Http\Requests\Admin\UpdateProductTypeRequest;
use App\Models\ProductType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->toString());

        $productTypes = ProductType::query()
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (ProductType $productType) => [
                ...$productType->only(['id', 'name', 'slug', 'description', 'is_active']),
                'products_count' => $productType->products_count,
            ]);

        return Inertia::render('admin/product-types/Index', [
            'productTypes' => $productTypes,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/product-types/Create');
    }

    public function store(StoreProductTypeRequest $request): RedirectResponse
    {
        ProductType::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Type de produit créé avec succès.']);

        return to_route('admin.product-types.index');
    }

    public function edit(ProductType $productType): Response
    {
        return Inertia::render('admin/product-types/Edit', [
            'productType' => $productType->only(['id', 'name', 'description', 'is_active']),
        ]);
    }

    public function update(UpdateProductTypeRequest $request, ProductType $productType): RedirectResponse
    {
        $productType->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Type de produit mis à jour.']);

        return to_route('admin.product-types.index');
    }

    public function destroy(ProductType $productType): RedirectResponse
    {
        if ($productType->products()->withTrashed()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ce type est utilisé par des produits et ne peut pas être supprimé.',
            ]);

            return back();
        }

        $productType->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Type de produit supprimé.']);

        return to_route('admin.product-types.index');
    }

    public function toggle(ProductType $productType): RedirectResponse
    {
        $productType->update(['is_active' => ! $productType->is_active]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Statut du type mis à jour.']);

        return back();
    }
}
