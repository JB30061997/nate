<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Requests\Admin\UpdateProductSliderVisibilityRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => trim($request->string('search')->toString()),
            'category' => $request->string('category')->toString(),
            'product_type' => $request->string('product_type')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $products = Product::query()
            ->with(['category:id,name', 'productType:id,name'])
            ->withCount('images')
            ->when($filters['search'] !== '', fn ($query) => $query->where('name', 'like', "%{$filters['search']}%"))
            ->when($filters['category'] !== '', fn ($query) => $query->where('category_id', $filters['category']))
            ->when($filters['product_type'] !== '', fn ($query) => $query->where('product_type_id', $filters['product_type']))
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $product) => [
                ...$product->only(['id', 'name', 'sku', 'price', 'compare_at_price', 'stock_quantity', 'is_active', 'is_featured', 'show_in_slider']),
                'category' => $product->category->only(['id', 'name']),
                'product_type' => $product->productType->only(['id', 'name']),
                'main_image_url' => $this->mainImageUrl($product->main_image),
                'images_count' => $product->images_count,
            ]);

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'sliderSelectionEnabled' => Schema::hasColumn('products', 'show_in_slider'),
            'filters' => $filters,
            'categories' => $this->categoryOptions(),
            'productTypes' => $this->productTypeOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/Create', [
            'categories' => $this->categoryOptions(),
            'productTypes' => $this->productTypeOptions(),
            'productOptions' => Schema::hasColumn('products', 'is_pack') ? $this->productOptions() : [],
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['images', 'main_image_url', 'pack_items']);
        $this->removeUnavailableMerchandisingFields($data);
        $data['main_image'] = $request->validated('main_image_url');
        $storedPaths = [];

        try {
            $product = DB::transaction(function () use ($request, $data, &$storedPaths): Product {
                $product = Product::query()->create($data);
                $this->storeImages($product, $request->file('images', []), $storedPaths);

                if (! $product->main_image && $storedPaths !== []) {
                    $product->update(['main_image' => $storedPaths[0]]);
                }

                if (Schema::hasTable('product_pack_items')) {
                    $this->syncPackItems($product, $request->validated('pack_items', []));
                }

                return $product;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Produit créé avec succès.']);

        return to_route('admin.products.edit', $product);
    }

    public function edit(Product $product): Response
    {
        $product->load(['images' => fn ($query) => $query->orderBy('sort_order')]);

        if (Schema::hasTable('product_pack_items')) {
            $product->load('packItems:id,name');
        }

        return Inertia::render('admin/products/Edit', [
            'product' => [
                ...$product->only([
                    'id',
                    'category_id',
                    'product_type_id',
                    'name',
                    'sku',
                    'description',
                    'usage_instructions',
                    'ingredients',
                    'precautions',
                    'price',
                    'compare_at_price',
                    'stock_quantity',
                    'is_active',
                    'is_featured',
                    'is_pack',
                    'main_image',
                ]),
                'main_image_url' => str_starts_with((string) $product->main_image, 'https://')
                    ? $product->main_image
                    : null,
                'main_image_preview_url' => $this->mainImageUrl($product->main_image),
                'images' => $product->images->map(fn ($image) => [
                    ...$image->only(['id', 'image_path', 'sort_order']),
                    'url' => Storage::disk('public')->url($image->image_path),
                    'is_main' => $image->image_path === $product->main_image,
                ])->values(),
                'pack_items' => ($product->relationLoaded('packItems') ? $product->packItems : collect())->map(fn (Product $item): array => [
                    'product_id' => $item->id,
                    'quantity' => (int) $item->pivot->quantity,
                ])->values(),
            ],
            'categories' => $this->categoryOptions(),
            'productTypes' => $this->productTypeOptions(),
            'productOptions' => Schema::hasColumn('products', 'is_pack') ? $this->productOptions($product->id) : [],
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $images = $request->file('images', []);

        if ($product->images()->count() + count($images) > 8) {
            throw ValidationException::withMessages([
                'images' => 'Un produit ne peut pas contenir plus de 8 images.',
            ]);
        }

        $data = $request->safe()->except(['images', 'main_image_url', 'pack_items']);
        $this->removeUnavailableMerchandisingFields($data);
        $mainImageUrl = $request->validated('main_image_url');
        $hadExternalMainImage = str_starts_with((string) $product->main_image, 'https://');
        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $product, $data, $images, $mainImageUrl, $hadExternalMainImage, &$storedPaths): void {
                $product->update($data);
                $this->storeImages($product, $images, $storedPaths);

                if ($mainImageUrl) {
                    $product->update(['main_image' => $mainImageUrl]);
                } elseif ($hadExternalMainImage) {
                    $product->update([
                        'main_image' => $product->images()->orderBy('sort_order')->value('image_path'),
                    ]);
                } elseif (! $product->main_image && $storedPaths !== []) {
                    $product->update(['main_image' => $storedPaths[0]]);
                }

                if (Schema::hasTable('product_pack_items')) {
                    $this->syncPackItems($product, $request->validated('pack_items', []));
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Produit mis à jour.']);

        return to_route('admin.products.edit', $product);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Produit supprimé.']);

        return to_route('admin.products.index');
    }

    public function updateSliderVisibility(UpdateProductSliderVisibilityRequest $request, Product $product): RedirectResponse
    {
        if (! Schema::hasColumn('products', 'show_in_slider')) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'La migration du slider doit être exécutée avant de modifier cette sélection.',
            ]);

            return back()->withErrors([
                'show_in_slider' => 'La colonne show_in_slider n’existe pas encore dans la base de données.',
            ]);
        }

        $product->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $product->show_in_slider
                ? 'Le produit apparaîtra dans le slider.'
                : 'Le produit a été retiré du slider.',
        ]);

        return back();
    }

    /**
     * @param  array<int, UploadedFile>  $images
     * @param  array<int, string>  $storedPaths
     */
    private function storeImages(Product $product, array $images, array &$storedPaths): void
    {
        $sortOrder = (int) $product->images()->max('sort_order');

        foreach ($images as $image) {
            $path = $image->store("products/{$product->id}", 'public');
            $storedPaths[] = $path;

            $product->images()->create([
                'image_path' => $path,
                'sort_order' => ++$sortOrder,
            ]);
        }

    }

    /** @return array<int, array{id: int, name: string, is_active: bool}> */
    private function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active'])
            ->map(fn (Category $category) => Arr::only($category->toArray(), ['id', 'name', 'is_active']))
            ->all();
    }

    /** @return array<int, array{id: int, name: string, is_active: bool}> */
    private function productTypeOptions(): array
    {
        return ProductType::query()
            ->orderBy('name')
            ->get(['id', 'name', 'is_active'])
            ->map(fn (ProductType $productType) => Arr::only($productType->toArray(), ['id', 'name', 'is_active']))
            ->all();
    }

    /** @return array<int, array{id: int, name: string, stock_quantity: int}> */
    private function productOptions(?int $exceptId = null): array
    {
        return Product::query()
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where('is_pack', false)
            ->orderBy('name')
            ->get(['id', 'name', 'stock_quantity'])
            ->map(fn (Product $product): array => $product->only(['id', 'name', 'stock_quantity']))
            ->all();
    }

    /** @param array<string, mixed> $data */
    private function removeUnavailableMerchandisingFields(array &$data): void
    {
        if (! Schema::hasColumn('products', 'is_pack')) {
            Arr::forget($data, ['usage_instructions', 'ingredients', 'precautions', 'is_pack']);
        }
    }

    /** @param array<int, array{product_id: int, quantity: int}> $items */
    private function syncPackItems(Product $product, array $items): void
    {
        if (! $product->is_pack) {
            $product->packItems()->detach();

            return;
        }

        $product->packItems()->sync(collect($items)->mapWithKeys(
            fn (array $item): array => [(int) $item['product_id'] => ['quantity' => (int) $item['quantity']]],
        )->all());
    }

    private function mainImageUrl(?string $mainImage): ?string
    {
        if (! $mainImage) {
            return null;
        }

        return str_starts_with($mainImage, 'https://')
            ? $mainImage
            : Storage::disk('public')->url($mainImage);
    }
}
