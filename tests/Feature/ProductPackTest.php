<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPackTest extends TestCase
{
    use RefreshDatabase;

    public function test_pack_order_decrements_pack_and_component_stock_atomically(): void
    {
        [$pack, $component] = $this->createPack(componentStock: 6, componentQuantity: 2);
        $this->withSession($this->sessionData($pack))->post(route('checkout.store'), $this->payload())->assertRedirect();

        $this->assertSame(3, $pack->fresh()->stock_quantity);
        $this->assertSame(2, $component->fresh()->stock_quantity);
        $this->assertSame(1, Order::query()->count());
    }

    public function test_pack_order_is_rejected_when_a_component_is_insufficient(): void
    {
        [$pack, $component] = $this->createPack(componentStock: 3, componentQuantity: 2);
        $this->withSession($this->sessionData($pack))->post(route('checkout.store'), $this->payload())->assertSessionHasErrors('cart');

        $this->assertSame(5, $pack->fresh()->stock_quantity);
        $this->assertSame(3, $component->fresh()->stock_quantity);
        $this->assertDatabaseEmpty('orders');
    }

    /** @return array{Product, Product} */
    private function createPack(int $componentStock, int $componentQuantity): array
    {
        $category = Category::query()->create(['name' => 'Packs', 'slug' => 'packs']);
        $type = ProductType::query()->create(['name' => 'Pack', 'slug' => 'pack']);
        $base = ['category_id' => $category->id, 'product_type_id' => $type->id, 'price' => '100.00', 'is_active' => true];
        $component = Product::query()->create([...$base, 'name' => 'Composant', 'slug' => 'composant', 'stock_quantity' => $componentStock]);
        $pack = Product::query()->create([...$base, 'name' => 'Pack soin', 'slug' => 'pack-soin', 'stock_quantity' => 5, 'is_pack' => true]);
        $pack->packItems()->attach($component->id, ['quantity' => $componentQuantity]);

        return [$pack, $component];
    }

    /** @return array<string, mixed> */
    private function sessionData(Product $pack): array
    {
        return ['store_cart' => [(string) $pack->id => 2], 'checkout_token' => '123e4567-e89b-42d3-a456-426614174000'];
    }

    /** @return array<string, string> */
    private function payload(): array
    {
        return [
            'customer_name' => 'Cliente NATE', 'customer_phone' => '0612345678',
            'customer_city' => 'Casablanca', 'customer_address' => 'Adresse test',
            'customer_notes' => '', 'checkout_token' => '123e4567-e89b-42d3-a456-426614174000',
        ];
    }
}
