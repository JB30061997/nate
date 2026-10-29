<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function index(Request $request): Response
    {
        $reviewsEnabled = Schema::hasTable('reviews');
        $sliderSelectionEnabled = Schema::hasColumn('products', 'show_in_slider');
        $merchandisingEnabled = Schema::hasColumn('products', 'is_pack') && Schema::hasTable('product_pack_items');
        $productQuery = Product::query()
            ->with([
                'category:id,name,slug,image',
                'images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->latest('id');

        if ($merchandisingEnabled) {
            $productQuery->with('packItems:id,name,stock_quantity,is_active');
        }

        if ($reviewsEnabled) {
            $productQuery
                ->with(['reviews' => fn ($query) => $query->where('status', 'approved')->latest()->limit(6)])
                ->withAvg(['reviews as approved_reviews_avg_rating' => fn ($query) => $query->where('status', 'approved')], 'rating')
                ->withCount(['reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved')]);
        }

        $productColumns = ['id', 'category_id', 'product_type_id', 'name', 'slug', 'description', 'price', 'compare_at_price', 'stock_quantity', 'main_image', 'is_featured'];

        if ($sliderSelectionEnabled) {
            $productColumns[] = 'show_in_slider';
        }

        if ($merchandisingEnabled) {
            array_push($productColumns, 'usage_instructions', 'ingredients', 'precautions', 'is_pack');
        }

        $products = $productQuery
            ->get($productColumns)
            ->map(fn (Product $product): array => $this->productPayload($product, $reviewsEnabled, $merchandisingEnabled))
            ->values();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'image'])
            ->map(function (Category $category): Category {
                if ($category->image) {
                    $category->image = $this->imageUrl($category->image);
                }

                return $category;
            });
        $cart = $this->cartLines($request);

        return Inertia::render('Welcome', [
            'products' => $products,
            'sliderProducts' => $sliderSelectionEnabled
                ? $products
                    ->where('show_in_slider', true)
                    ->filter(fn (array $product): bool => filled($product['main_image']))
                    ->values()
                : [],
            'categories' => $categories,
            'cart' => $cart,
            'cartCount' => array_sum(array_column($cart, 'quantity')),
            'storeInfo' => Schema::hasTable('store_settings')
                ? StoreSetting::values(['whatsapp', 'instagram', 'phone', 'email'])
                : [],
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        $product = Product::query()
            ->when(Schema::hasTable('product_pack_items'), fn ($query) => $query->with('packItems'))
            ->where('is_active', true)
            ->findOrFail($data['product_id']);

        $availableStock = $this->effectiveStock($product);

        if ((float) $product->price <= 0 || $availableStock <= 0) {
            throw ValidationException::withMessages(['product_id' => 'Ce produit est indisponible.']);
        }

        $cart = $request->session()->get('store_cart', []);
        $id = (string) $product->id;
        $quantity = (int) ($cart[$id] ?? 0) + (int) $data['quantity'];

        if ($quantity > $availableStock) {
            throw ValidationException::withMessages(['quantity' => 'Stock insuffisant.']);
        }

        $cart[$id] = $quantity;
        $request->session()->put('store_cart', $cart);

        return back()->with('success', 'Produit ajouté au panier.');
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $item = Product::query()
            ->when(Schema::hasTable('product_pack_items'), fn ($query) => $query->with('packItems'))
            ->where('is_active', true)
            ->findOrFail($product);

        if ((float) $item->price <= 0 || $this->effectiveStock($item) < $data['quantity']) {
            throw ValidationException::withMessages(['quantity' => 'Quantité non disponible.']);
        }

        $cart = $request->session()->get('store_cart', []);
        abort_unless(isset($cart[(string) $product]), 404);
        $cart[(string) $product] = (int) $data['quantity'];
        $request->session()->put('store_cart', $cart);

        return back();
    }

    public function remove(Request $request, int $product): RedirectResponse
    {
        $cart = $request->session()->get('store_cart', []);
        unset($cart[(string) $product]);
        $request->session()->put('store_cart', $cart);

        return back();
    }

    /** @return array<string, mixed> */
    private function productPayload(Product $product, bool $reviewsEnabled, bool $merchandisingEnabled): array
    {
        $gallery = collect([$product->main_image])
            ->merge($product->images->pluck('image_path'))
            ->filter()
            ->unique()
            ->map(fn (string $path): string => $this->imageUrl($path))
            ->values();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
            'stock_quantity' => $this->effectiveStock($product),
            'main_image' => $gallery->first(),
            'main_image_mode' => $gallery->first() ? $this->imagePresentationMode($gallery->first()) : 'packshot',
            'images' => $gallery,
            'is_featured' => $product->is_featured,
            'show_in_slider' => (bool) ($product->show_in_slider ?? false),
            'category_id' => $product->category_id,
            'category' => $product->category,
            'reviews_enabled' => $reviewsEnabled,
            'rating_average' => $reviewsEnabled && $product->approved_reviews_avg_rating !== null ? round((float) $product->approved_reviews_avg_rating, 1) : null,
            'reviews_count' => $reviewsEnabled ? (int) $product->approved_reviews_count : 0,
            'reviews' => ($product->relationLoaded('reviews') ? $product->reviews : collect())->map(fn ($review): array => [
                'id' => $review->id,
                'customer_name' => $review->customer_name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toDateString(),
            ])->values(),
            'usage_instructions' => $merchandisingEnabled ? $product->usage_instructions : null,
            'ingredients' => $merchandisingEnabled ? $product->ingredients : null,
            'precautions' => $merchandisingEnabled ? $product->precautions : null,
            'is_pack' => $merchandisingEnabled && $product->is_pack,
            'pack_items' => $merchandisingEnabled && $product->relationLoaded('packItems')
                ? $product->packItems->map(fn (Product $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => (int) $item->pivot->quantity,
                ])->values()
                : [],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function cartLines(Request $request): array
    {
        $stored = $request->session()->get('store_cart', []);

        if ($stored === []) {
            return [];
        }

        return Product::query()
            ->when(Schema::hasTable('product_pack_items'), fn ($query) => $query->with('packItems'))
            ->whereIn('id', array_keys($stored))
            ->where('is_active', true)
            ->get()
            ->filter(fn (Product $product): bool => (float) $product->price > 0 && $this->effectiveStock($product) > 0)
            ->map(function (Product $product) use ($stored): ?array {
                $availableStock = $this->effectiveStock($product);
                $quantity = min((int) ($stored[(string) $product->id] ?? 0), $availableStock);

                if ($quantity < 1) {
                    return null;
                }

                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'quantity' => $quantity,
                    'image' => $product->main_image ? $this->imageUrl($product->main_image) : null,
                    'stock' => $availableStock,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function effectiveStock(Product $product): int
    {
        if (! Schema::hasColumn('products', 'is_pack') || ! $product->is_pack) {
            return (int) $product->stock_quantity;
        }

        if (! $product->relationLoaded('packItems')) {
            $product->load('packItems');
        }

        if ($product->packItems->isEmpty()) {
            return 0;
        }

        $componentStock = $product->packItems->min(
            fn (Product $item): int => ! $item->trashed() && $item->is_active
                ? intdiv((int) $item->stock_quantity, max(1, (int) $item->pivot->quantity))
                : 0,
        );

        return min((int) $product->stock_quantity, (int) $componentStock);
    }

    private function imageUrl(string $path): string
    {
        if (str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.ltrim(preg_replace('#^storage/#', '', $path) ?? $path, '/');
    }

    private function imagePresentationMode(string $path): string
    {
        $extension = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?? $path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg'], true) ? 'editorial' : 'packshot';
    }
}
