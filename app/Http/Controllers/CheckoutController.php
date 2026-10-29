<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $cart = $this->cartLines($request);

        if ($cart === []) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'سلة التسوق فارغة.']);

            return to_route('home');
        }

        $checkoutToken = (string) $request->session()->get('checkout_token');

        if ($checkoutToken === '') {
            $checkoutToken = (string) Str::uuid();
            $request->session()->put('checkout_token', $checkoutToken);
        }

        return Inertia::render('Checkout', [
            'cart' => $cart,
            'subtotal' => $this->subtotal($cart),
            'deliveryFee' => $this->deliveryFee(),
            'checkoutToken' => $checkoutToken,
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $token = $request->validated('checkout_token');
        $sessionToken = $request->session()->get('checkout_token');

        if (! is_string($sessionToken) || ! hash_equals($sessionToken, $token)) {
            throw ValidationException::withMessages([
                'checkout_token' => 'انتهت جلسة الطلب. أعيدي تحميل الصفحة.',
            ]);
        }

        $externalReference = $this->externalReference($token);
        $supportsShortReferences = Schema::hasColumn('orders', 'external_reference');

        /** @var RedirectResponse $response */
        $response = Cache::lock("checkout:{$token}", 15)->block(5, function () use ($request, $externalReference, $supportsShortReferences): RedirectResponse {
            $existingOrder = Order::query()
                ->where($supportsShortReferences ? 'external_reference' : 'order_number', $externalReference)
                ->first();

            if ($existingOrder) {
                return $this->completeCheckout($request, $existingOrder);
            }

            $storedCart = $request->session()->get('store_cart', []);

            if (! is_array($storedCart) || $storedCart === []) {
                throw ValidationException::withMessages([
                    'cart' => 'سلة التسوق فارغة.',
                ]);
            }

            $order = DB::transaction(function () use ($request, $storedCart, $externalReference, $supportsShortReferences): Order {
                $quantities = collect($storedCart)
                    ->mapWithKeys(fn ($quantity, $productId) => [(int) $productId => (int) $quantity])
                    ->filter(fn (int $quantity) => $quantity > 0);

                if ($quantities->isEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => 'سلة التسوق فارغة.',
                    ]);
                }

                $packsEnabled = Schema::hasColumn('products', 'is_pack') && Schema::hasTable('product_pack_items');
                $products = Product::withTrashed()
                    ->when($packsEnabled, fn ($query) => $query->with('packItems:id,name,stock_quantity,is_active'))
                    ->whereIn('id', $quantities->keys())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $this->validateLockedProducts($products, $quantities);

                $stockDemands = $quantities->map(fn (int $quantity): int => $quantity);

                if ($packsEnabled) {
                    foreach ($quantities as $productId => $quantity) {
                        /** @var Product $product */
                        $product = $products->get($productId);

                        if (! $product->is_pack) {
                            continue;
                        }

                        if ($product->packItems->isEmpty()) {
                            throw ValidationException::withMessages(['cart' => "Le pack « {$product->name} » ne contient aucun produit."]);
                        }

                        foreach ($product->packItems as $item) {
                            $required = $quantity * (int) $item->pivot->quantity;
                            $stockDemands->put($item->id, (int) $stockDemands->get($item->id, 0) + $required);
                        }
                    }
                }

                $stockProducts = Product::withTrashed()
                    ->whereIn('id', $stockDemands->keys())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($stockDemands as $productId => $required) {
                    /** @var Product|null $stockProduct */
                    $stockProduct = $stockProducts->get($productId);

                    if (! $stockProduct || $stockProduct->trashed() || ! $stockProduct->is_active || $required > $stockProduct->stock_quantity) {
                        throw ValidationException::withMessages([
                            'cart' => 'Le stock d’un produit ou d’un composant de pack est insuffisant.',
                        ]);
                    }
                }

                $lines = [];
                $subtotalInCents = 0;

                foreach ($quantities as $productId => $quantity) {
                    /** @var Product $product */
                    $product = $products->get($productId);
                    $unitPriceInCents = (int) round((float) $product->price * 100);
                    $lineTotalInCents = $unitPriceInCents * $quantity;
                    $subtotalInCents += $lineTotalInCents;

                    $lines[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $this->moneyFromCents($unitPriceInCents),
                        'quantity' => $quantity,
                        'line_total' => $this->moneyFromCents($lineTotalInCents),
                    ];
                }

                $deliveryFeeInCents = (int) round($this->deliveryFee() * 100);
                $orderAttributes = [
                    ...$request->safe()->only([
                        'customer_name',
                        'customer_phone',
                        'customer_city',
                        'customer_address',
                        'customer_notes',
                    ]),
                    'order_number' => $externalReference,
                    'subtotal' => $this->moneyFromCents($subtotalInCents),
                    'delivery_fee' => $this->moneyFromCents($deliveryFeeInCents),
                    'total' => $this->moneyFromCents($subtotalInCents + $deliveryFeeInCents),
                    'status' => 'pending',
                ];

                if ($supportsShortReferences) {
                    $orderAttributes['external_reference'] = $externalReference;
                }

                $order = Order::query()->create($orderAttributes);

                if ($supportsShortReferences) {
                    $order->update([
                        'order_number' => Order::shortReference((int) $order->getKey()),
                    ]);
                }

                $order->items()->createMany($lines);

                foreach ($stockDemands as $productId => $quantity) {
                    /** @var Product $product */
                    $product = $stockProducts->get($productId);
                    $product->update([
                        'stock_quantity' => $product->stock_quantity - $quantity,
                    ]);
                }

                return $order;
            }, 3);

            return $this->completeCheckout($request, $order);
        });

        return $response;
    }

    public function confirmation(Request $request, string $orderNumber): Response
    {
        abort_unless(
            hash_equals((string) $request->session()->get('confirmed_order_number'), $orderNumber),
            404,
        );

        $order = Order::query()
            ->with('items:id,order_id,product_name,unit_price,quantity,line_total')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return Inertia::render('OrderConfirmation', [
            'order' => [
                ...$order->only([
                    'order_number',
                    'customer_name',
                    'customer_phone',
                    'customer_city',
                    'customer_address',
                    'subtotal',
                    'delivery_fee',
                    'total',
                    'status',
                ]),
                'items' => $order->items->map->only([
                    'product_name',
                    'unit_price',
                    'quantity',
                    'line_total',
                ])->values(),
            ],
        ]);
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, int>  $quantities
     */
    private function validateLockedProducts(Collection $products, Collection $quantities): void
    {
        if ($products->count() !== $quantities->count()) {
            throw ValidationException::withMessages([
                'cart' => 'أحد المنتجات الموجودة في سلتكِ لم يعد متوفراً.',
            ]);
        }

        foreach ($quantities as $productId => $quantity) {
            /** @var Product|null $product */
            $product = $products->get($productId);

            if (! $product || $product->trashed() || ! $product->is_active || $product->price === null || (float) $product->price <= 0) {
                throw ValidationException::withMessages([
                    'cart' => 'لا يمكن طلب المنتج « '.($product?->name ?? 'غير متوفر').' » حالياً.',
                ]);
            }

            if ($quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'cart' => "الكمية المتوفرة من « {$product->name} » غير كافية: المتوفر {$product->stock_quantity}.",
                ]);
            }
        }
    }

    private function completeCheckout(Request $request, Order $order): RedirectResponse
    {
        $request->session()->forget(['store_cart', 'checkout_token']);
        $request->session()->put('confirmed_order_number', $order->order_number);

        return to_route('checkout.confirmation', $order->order_number);
    }

    /** @return array<int, array<string, int|float|string|null>> */
    private function cartLines(Request $request): array
    {
        $storedCart = $request->session()->get('store_cart', []);

        if (! is_array($storedCart) || $storedCart === []) {
            return [];
        }

        $products = Product::query()
            ->when(Schema::hasTable('product_pack_items'), fn ($query) => $query->with('packItems'))
            ->whereIn('id', array_keys($storedCart))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach ($storedCart as $productId => $quantity) {
            /** @var Product|null $product */
            $product = $products->get((int) $productId);

            $availableStock = $product ? $this->effectiveStock($product) : 0;

            if (! $product || (float) $product->price <= 0 || (int) $quantity < 1 || (int) $quantity > $availableStock) {
                continue;
            }

            $lines[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => (int) $quantity,
                'image' => $this->imageUrl($product->main_image),
                'stock' => $availableStock,
                'line_total' => (float) $product->price * (int) $quantity,
            ];
        }

        return $lines;
    }

    /** @param array<int, array<string, int|float|string|null>> $cart */
    private function subtotal(array $cart): float
    {
        return array_reduce(
            $cart,
            fn (float $subtotal, array $line): float => $subtotal + (float) $line['line_total'],
            0.0,
        );
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

        return min(
            (int) $product->stock_quantity,
            (int) $product->packItems->min(fn (Product $item): int => ! $item->trashed() && $item->is_active
                ? intdiv((int) $item->stock_quantity, max(1, (int) $item->pivot->quantity))
                : 0),
        );
    }

    private function imageUrl(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        return str_starts_with($image, 'https://')
            ? $image
            : Storage::disk('public')->url($image);
    }

    private function externalReference(string $token): string
    {
        return 'NATE-'.Str::upper(Str::replace('-', '', $token));
    }

    private function moneyFromCents(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }

    private function deliveryFee(): float
    {
        if (! Schema::hasTable('store_settings')) {
            return 0.0;
        }

        return max(0, (float) StoreSetting::query()->where('key', 'delivery_fee')->value('value'));
    }
}
