<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductType;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_expose_the_expected_relationships(): void
    {
        [$category, $productType, $product] = $this->createProduct();
        $image = ProductImage::query()->create([
            'product_id' => $product->id,
            'image_path' => 'products/serum-side.jpg',
            'sort_order' => 1,
        ]);
        $cart = Cart::query()->create(['session_token' => 'session-relations']);
        $cartItem = CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $order = $this->createOrder();
        $orderItem = $this->createOrderItem($order, $product);

        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($productType->products->contains($product));
        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->productType->is($productType));
        $this->assertTrue($product->images->contains($image));
        $this->assertTrue($product->cartItems->contains($cartItem));
        $this->assertTrue($product->orderItems->contains($orderItem));
        $this->assertTrue($image->product->is($product));
        $this->assertTrue($cart->items->contains($cartItem));
        $this->assertTrue($cartItem->cart->is($cart));
        $this->assertTrue($cartItem->product->is($product));
        $this->assertTrue($order->items->contains($orderItem));
        $this->assertTrue($orderItem->order->is($order));
        $this->assertTrue($orderItem->product->is($product));
    }

    public function test_dependent_records_are_deleted_by_cascade(): void
    {
        [, , $product] = $this->createProduct();
        $image = ProductImage::query()->create([
            'product_id' => $product->id,
            'image_path' => 'products/serum.jpg',
        ]);
        $cart = Cart::query()->create(['session_token' => 'session-cascade']);
        $cartItem = CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $order = $this->createOrder();
        $orderItem = $this->createOrderItem($order, $product);

        $cart->delete();
        $order->delete();
        $product->forceDelete();

        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    public function test_category_and_product_type_in_use_cannot_be_deleted(): void
    {
        [$category, $productType] = $this->createProduct();

        try {
            $category->delete();
            $this->fail('Deleting a category still used by a product should fail.');
        } catch (QueryException) {
            $this->assertDatabaseHas('categories', ['id' => $category->id]);
        }

        $this->expectException(QueryException::class);
        $productType->delete();
    }

    public function test_a_product_can_only_appear_once_in_a_cart(): void
    {
        [, , $product] = $this->createProduct();
        $cart = Cart::query()->create(['session_token' => 'session-unique']);
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->expectException(QueryException::class);

        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_order_item_snapshots_survive_product_changes_and_deletion(): void
    {
        [, , $product] = $this->createProduct();
        $order = $this->createOrder();
        $orderItem = $this->createOrderItem($order, $product);

        $product->update([
            'name' => 'Sérum renommé',
            'price' => '199.00',
        ]);

        $this->assertSame('Sérum hydratant', $orderItem->fresh()->product_name);
        $this->assertSame('129.90', $orderItem->fresh()->unit_price);

        $product->forceDelete();
        $orderItem->refresh();

        $this->assertNull($orderItem->product_id);
        $this->assertSame('Sérum hydratant', $orderItem->product_name);
        $this->assertSame('129.90', $orderItem->unit_price);
        $this->assertSame('259.80', $orderItem->line_total);
        $this->assertTrue($orderItem->order->is($order));
    }

    /** @return array{Category, ProductType, Product} */
    private function createProduct(): array
    {
        $category = Category::query()->create([
            'name' => 'Soins visage',
            'slug' => 'soins-visage',
        ]);
        $productType = ProductType::query()->create([
            'name' => 'Sérum',
            'slug' => 'serum',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $productType->id,
            'name' => 'Sérum hydratant',
            'slug' => 'serum-hydratant',
            'sku' => 'SERUM-001',
            'price' => '129.90',
            'stock_quantity' => 12,
        ]);

        return [$category, $productType, $product];
    }

    private function createOrder(): Order
    {
        return Order::query()->create([
            'order_number' => 'NATE-000001',
            'customer_name' => 'Cliente Test',
            'customer_phone' => '0600000000',
            'subtotal' => '259.80',
            'delivery_fee' => '20.00',
            'total' => '279.80',
        ]);
    }

    private function createOrderItem(Order $order, Product $product): OrderItem
    {
        return $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 2,
            'line_total' => '259.80',
        ]);
    }
}
