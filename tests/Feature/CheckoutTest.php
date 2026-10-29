<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = '123e4567-e89b-42d3-a456-426614174000';

    public function test_checkout_page_displays_the_session_cart(): void
    {
        $product = $this->createProduct();

        $this->withSession(['store_cart' => [(string) $product->id => 2]])
            ->get(route('checkout.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Checkout')
                ->where('cart.0.product_id', $product->id)
                ->where('cart.0.quantity', 2)
                ->where('subtotal', 259.8)
                ->has('checkoutToken')
            );
    }

    public function test_empty_cart_is_redirected_to_the_storefront(): void
    {
        $this->get(route('checkout.show'))
            ->assertRedirect(route('home'));
    }

    public function test_checkout_creates_real_order_snapshots_and_decrements_stock(): void
    {
        $product = $this->createProduct();

        $response = $this
            ->withSession([
                'store_cart' => [(string) $product->id => 2],
                'checkout_token' => self::TOKEN,
            ])
            ->post(route('checkout.store'), $this->validPayload());

        $order = Order::query()->with('items')->firstOrFail();

        $response->assertRedirect(route('checkout.confirmation', $order->order_number));
        $response->assertSessionMissing('store_cart');
        $this->assertSame('NATE-000001', $order->order_number);
        $this->assertSame('NATE-123E4567E89B42D3A456426614174000', $order->external_reference);
        $this->assertSame('pending', $order->status);
        $this->assertSame('259.80', $order->subtotal);
        $this->assertSame('259.80', $order->total);
        $this->assertCount(1, $order->items);
        $this->assertSame('Sérum hydratant', $order->items[0]->product_name);
        $this->assertSame('129.90', $order->items[0]->unit_price);
        $this->assertSame(2, $order->items[0]->quantity);
        $this->assertSame('259.80', $order->items[0]->line_total);
        $this->assertSame(3, $product->fresh()->stock_quantity);

        $this->get(route('checkout.confirmation', $order->order_number))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OrderConfirmation')
                ->where('order.order_number', $order->order_number)
            );
    }

    public function test_checkout_rejects_insufficient_stock_and_keeps_cart(): void
    {
        $product = $this->createProduct();

        $response = $this
            ->withSession([
                'store_cart' => [(string) $product->id => 6],
                'checkout_token' => self::TOKEN,
            ])
            ->post(route('checkout.store'), $this->validPayload());

        $response->assertSessionHasErrors('cart');
        $response->assertSessionHas('store_cart', [(string) $product->id => 6]);
        $this->assertDatabaseEmpty('orders');
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_checkout_rejects_inactive_product(): void
    {
        $product = $this->createProduct(['is_active' => false]);

        $this->withSession([
            'store_cart' => [(string) $product->id => 1],
            'checkout_token' => self::TOKEN,
        ])
            ->post(route('checkout.store'), $this->validPayload())
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseEmpty('orders');
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_checkout_token_prevents_duplicate_orders_and_stock_updates(): void
    {
        $product = $this->createProduct();
        $session = [
            'store_cart' => [(string) $product->id => 1],
            'checkout_token' => self::TOKEN,
        ];

        $this->withSession($session)
            ->post(route('checkout.store'), $this->validPayload())
            ->assertRedirect();

        $this->withSession($session)
            ->post(route('checkout.store'), $this->validPayload())
            ->assertRedirect();

        $this->assertSame(1, Order::query()->count());
        $order = Order::query()->firstOrFail();
        $this->assertSame('NATE-000001', $order->order_number);
        $this->assertSame('NATE-123E4567E89B42D3A456426614174000', $order->external_reference);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame(4, $product->fresh()->stock_quantity);
    }

    public function test_checkout_validates_moroccan_phone_and_required_address(): void
    {
        $product = $this->createProduct();

        $this->withSession([
            'store_cart' => [(string) $product->id => 1],
            'checkout_token' => self::TOKEN,
        ])
            ->post(route('checkout.store'), [
                ...$this->validPayload(),
                'customer_phone' => '12345',
                'customer_address' => '',
            ])
            ->assertSessionHasErrors(['customer_phone', 'customer_address']);

        $this->assertDatabaseEmpty('orders');
    }

    public function test_checkout_uses_configured_delivery_fee_in_server_total(): void
    {
        $product = $this->createProduct();
        StoreSetting::query()->create(['key' => 'delivery_fee', 'value' => '25.00']);

        $this->withSession([
            'store_cart' => [(string) $product->id => 1],
            'checkout_token' => self::TOKEN,
        ])->post(route('checkout.store'), $this->validPayload())->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame('25.00', $order->delivery_fee);
        $this->assertSame('154.90', $order->total);
    }

    /** @param array<string, mixed> $overrides */
    private function createProduct(array $overrides = []): Product
    {
        $category = Category::query()->create([
            'name' => 'Visage',
            'slug' => 'visage',
        ]);
        $productType = ProductType::query()->create([
            'name' => 'Sérum',
            'slug' => 'serum',
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $productType->id,
            'name' => 'Sérum hydratant',
            'slug' => 'serum-hydratant',
            'price' => '129.90',
            'stock_quantity' => 5,
            'is_active' => true,
            ...$overrides,
        ]);
    }

    /** @return array<string, string> */
    private function validPayload(): array
    {
        return [
            'customer_name' => 'Cliente NATE',
            'customer_phone' => '0612345678',
            'customer_city' => 'Casablanca',
            'customer_address' => '12 rue des Fleurs',
            'customer_notes' => 'Appeler avant la livraison.',
            'checkout_token' => self::TOKEN,
        ];
    }
}
