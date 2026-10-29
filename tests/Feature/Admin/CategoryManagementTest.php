<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_access_category_management(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.categories.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/categories/Index'));
    }

    public function test_administrator_can_create_update_toggle_and_delete_a_category(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Soins Visage',
                'description' => 'Produits pour le visage.',
                'image' => UploadedFile::fake()->image('visage.jpg'),
                'is_active' => true,
                'sort_order' => 2,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->firstOrFail();

        $this->assertSame('soins-visage', $category->slug);
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Visage',
                'description' => 'Nouvelle description.',
                'is_active' => true,
                'sort_order' => 1,
                'remove_image' => false,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Visage',
            'slug' => 'visage',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.categories.toggle', $category))
            ->assertRedirect();

        $this->assertFalse($category->fresh()->is_active);

        $imagePath = $category->fresh()->image;

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_category_validation_rejects_duplicate_or_invalid_data(): void
    {
        $admin = User::factory()->admin()->create();
        Category::query()->create([
            'name' => 'Corps',
            'slug' => 'corps',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Corps',
                'is_active' => true,
                'sort_order' => -1,
            ])
            ->assertSessionHasErrors(['name', 'slug', 'sort_order']);

        $this->assertSame(1, Category::query()->count());
    }

    public function test_category_used_by_a_product_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        [$category] = $this->createProduct();

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @return array{Category, ProductType, Product} */
    private function createProduct(): array
    {
        $category = Category::query()->create([
            'name' => 'Visage',
            'slug' => 'visage',
        ]);
        $productType = ProductType::query()->create([
            'name' => 'Sérum',
            'slug' => 'serum',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $productType->id,
            'name' => 'Sérum doux',
            'slug' => 'serum-doux',
            'price' => '120.00',
        ]);

        return [$category, $productType, $product];
    }
}
