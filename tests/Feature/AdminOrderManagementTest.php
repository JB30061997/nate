<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrator_can_access_orders(): void
    {
        $this->get(route('admin.orders'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.orders'))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.orders'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/orders/Index'));
    }

    public function test_orders_page_exposes_real_details_images_and_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        $product = $this->createProduct();
        $order = $this->createOrder();
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Sérum Éclat - سيروم الإشراق',
            'unit_price' => '120.00',
            'quantity' => 2,
            'line_total' => '240.00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->where('stats.total', 1)
                ->where('stats.pending', 1)
                ->where('orders.data.0.order_number', $order->order_number)
                ->where('orders.data.0.items.0.product_name', 'Sérum Éclat - سيروم الإشراق')
                ->where('orders.data.0.items.0.image_url', 'https://images.example.com/serum.png')
            );
    }

    public function test_order_search_and_status_filters_remain_functional(): void
    {
        $admin = User::factory()->admin()->create();
        $matching = $this->createOrder(['order_number' => 'NATE-FIND-001', 'customer_name' => 'Sara Test', 'status' => 'pending']);
        $this->createOrder(['order_number' => 'NATE-OTHER-002', 'customer_name' => 'Autre Cliente', 'status' => 'delivered']);

        $this->actingAs($admin)
            ->get(route('admin.orders', ['search' => 'Sara', 'status' => 'pending']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('orders.data', 1)
                ->where('orders.data.0.id', $matching->id)
                ->where('filters.search', 'Sara')
                ->where('filters.status', 'pending')
            );
    }

    /** @param array<string, mixed> $overrides */
    private function createOrder(array $overrides = []): Order
    {
        return Order::query()->create([
            'order_number' => 'NATE-TEST-001',
            'customer_name' => 'Cliente NATÉ',
            'customer_phone' => '0612345678',
            'customer_city' => 'Casablanca',
            'customer_address' => '12 rue des Fleurs',
            'customer_notes' => 'Appeler avant la livraison.',
            'subtotal' => '240.00',
            'delivery_fee' => '20.00',
            'total' => '260.00',
            'status' => 'pending',
            ...$overrides,
        ]);
    }

    private function createProduct(): Product
    {
        $category = Category::query()->create(['name' => 'Visage', 'slug' => 'visage']);
        $type = ProductType::query()->create(['name' => 'Sérum', 'slug' => 'serum']);

        return Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $type->id,
            'name' => 'Sérum Éclat',
            'slug' => 'serum-eclat',
            'price' => '120.00',
            'main_image' => 'https://images.example.com/serum.png',
            'is_active' => true,
        ]);
    }
}
