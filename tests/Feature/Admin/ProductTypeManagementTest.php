<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_update_toggle_and_delete_a_product_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.product-types.store'), [
                'name' => 'Crème',
                'description' => 'Textures crémeuses.',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.product-types.index'));

        $productType = ProductType::query()->firstOrFail();

        $this->assertSame('creme', $productType->slug);

        $this->actingAs($admin)
            ->put(route('admin.product-types.update', $productType), [
                'name' => 'Crème visage',
                'description' => 'Pour le visage.',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.product-types.index'));

        $this->assertDatabaseHas('product_types', [
            'id' => $productType->id,
            'name' => 'Crème visage',
            'slug' => 'creme-visage',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.product-types.toggle', $productType))
            ->assertRedirect();

        $this->assertFalse($productType->fresh()->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.product-types.destroy', $productType))
            ->assertRedirect(route('admin.product-types.index'));

        $this->assertDatabaseMissing('product_types', ['id' => $productType->id]);
    }

    public function test_product_type_validation_rejects_duplicates(): void
    {
        $admin = User::factory()->admin()->create();
        ProductType::query()->create([
            'name' => 'Huile',
            'slug' => 'huile',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.product-types.store'), [
                'name' => 'Huile',
                'is_active' => true,
            ])
            ->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_product_type_used_by_a_product_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::query()->create([
            'name' => 'Cheveux',
            'slug' => 'cheveux',
        ]);
        $productType = ProductType::query()->create([
            'name' => 'Huile',
            'slug' => 'huile',
        ]);
        Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $productType->id,
            'name' => 'Huile nourrissante',
            'slug' => 'huile-nourrissante',
            'price' => '89.00',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.product-types.destroy', $productType))
            ->assertRedirect();

        $this->assertDatabaseHas('product_types', ['id' => $productType->id]);
    }

    public function test_non_admin_cannot_modify_product_types(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.product-types.store'), [
                'name' => 'Interdit',
                'is_active' => true,
            ])
            ->assertForbidden();

        $this->assertDatabaseEmpty('product_types');
    }
}
