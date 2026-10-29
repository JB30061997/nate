<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_product_with_multiple_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        [$category, $productType] = $this->createClassification();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'product_type_id' => $productType->id,
                'name' => 'Sérum Éclat',
                'sku' => 'SER-001',
                'description' => 'Un soin illuminateur.',
                'price' => '149.90',
                'compare_at_price' => '179.90',
                'stock_quantity' => 15,
                'is_active' => true,
                'is_featured' => true,
                'images' => [
                    UploadedFile::fake()->image('face.jpg'),
                    UploadedFile::fake()->image('side.webp'),
                ],
            ])
            ->assertRedirect();

        $product = Product::query()->firstOrFail();

        $this->assertSame('serum-eclat', $product->slug);
        $this->assertSame('149.90', $product->price);
        $this->assertTrue($product->is_featured);
        $this->assertSame(2, $product->images()->count());
        $this->assertNotNull($product->main_image);
        Storage::disk('public')->assertExists($product->main_image);
    }

    public function test_administrator_can_update_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        [$category, $productType] = $this->createClassification();
        $product = $this->createProduct($category, $productType);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'category_id' => $category->id,
                'product_type_id' => $productType->id,
                'name' => 'Sérum Nuit',
                'sku' => '',
                'description' => 'Nouvelle formule.',
                'price' => '160.00',
                'compare_at_price' => '190.00',
                'stock_quantity' => 4,
                'is_active' => false,
                'is_featured' => true,
            ])
            ->assertRedirect(route('admin.products.edit', $product));

        $product->refresh();

        $this->assertSame('Sérum Nuit', $product->name);
        $this->assertSame('serum-nuit', $product->slug);
        $this->assertNull($product->sku);
        $this->assertFalse($product->is_active);
        $this->assertTrue($product->is_featured);
    }

    public function test_administrator_can_use_an_external_https_main_image(): void
    {
        $admin = User::factory()->admin()->create();
        [$category, $productType] = $this->createClassification();
        $externalImage = 'https://images.example.com/products/serum.jpg';

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'product_type_id' => $productType->id,
                'name' => 'Sérum externe',
                'price' => '135.00',
                'stock_quantity' => 8,
                'is_active' => true,
                'is_featured' => false,
                'main_image_url' => $externalImage,
            ])
            ->assertRedirect();

        $product = Product::query()->firstOrFail();

        $this->assertSame($externalImage, $product->main_image);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.main_image_url', $externalImage)
            );

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertInertia(fn (Assert $page) => $page
                ->where('product.main_image_url', $externalImage)
                ->where('product.main_image_preview_url', $externalImage)
            );
    }

    public function test_external_main_image_must_use_https(): void
    {
        $admin = User::factory()->admin()->create();
        [$category, $productType] = $this->createClassification();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'product_type_id' => $productType->id,
                'name' => 'Image non sécurisée',
                'price' => '90.00',
                'stock_quantity' => 3,
                'is_active' => true,
                'is_featured' => false,
                'main_image_url' => 'http://images.example.com/product.jpg',
            ])
            ->assertSessionHasErrors('main_image_url');

        $this->assertDatabaseEmpty('products');
    }

    public function test_product_validation_rejects_invalid_prices_and_relationships(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => 999,
                'product_type_id' => 999,
                'name' => '',
                'price' => '-1',
                'compare_at_price' => '-2',
                'stock_quantity' => -1,
                'is_active' => true,
                'is_featured' => false,
            ])
            ->assertSessionHasErrors([
                'category_id',
                'product_type_id',
                'name',
                'price',
                'compare_at_price',
                'stock_quantity',
            ]);

        $this->assertDatabaseEmpty('products');
    }

    public function test_product_deletion_uses_soft_deletes_and_keeps_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        [$category, $productType] = $this->createClassification();
        $product = $this->createProduct($category, $productType);
        $path = UploadedFile::fake()->image('kept.jpg')->store("products/{$product->id}", 'public');
        $product->images()->create(['image_path' => $path]);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertDatabaseHas('product_images', ['product_id' => $product->id]);
        Storage::disk('public')->assertExists($path);
    }

    public function test_administrator_can_manage_product_images_safely(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        [$category, $productType] = $this->createClassification();
        $product = $this->createProduct($category, $productType);

        $this->actingAs($admin)
            ->post(route('admin.products.images.store', $product), [
                'images' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.png'),
                ],
            ])
            ->assertRedirect();

        $images = $product->images()->orderBy('sort_order')->get();

        $this->assertCount(2, $images);
        $this->assertSame($images[0]->image_path, $product->fresh()->main_image);

        $this->actingAs($admin)
            ->patch(route('admin.products.images.main', [$product, $images[1]]))
            ->assertRedirect();

        $this->assertSame($images[1]->image_path, $product->fresh()->main_image);

        $this->actingAs($admin)
            ->patch(route('admin.products.images.reorder', $product), [
                'image_ids' => [$images[1]->id, $images[0]->id],
            ])
            ->assertRedirect();

        $this->assertSame(
            [$images[1]->id, $images[0]->id],
            $product->images()->orderBy('sort_order')->pluck('id')->all(),
        );

        $deletedPath = $images[1]->image_path;

        $this->actingAs($admin)
            ->delete(route('admin.products.images.destroy', [$product, $images[1]]))
            ->assertRedirect();

        $this->assertDatabaseMissing('product_images', ['id' => $images[1]->id]);
        Storage::disk('public')->assertMissing($deletedPath);
        $this->assertSame($images[0]->image_path, $product->fresh()->main_image);
    }

    public function test_image_from_another_product_cannot_be_modified(): void
    {
        $admin = User::factory()->admin()->create();
        [$category, $productType] = $this->createClassification();
        $product = $this->createProduct($category, $productType);
        $otherProduct = Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $productType->id,
            'name' => 'Autre produit',
            'slug' => 'autre-produit',
            'price' => '50.00',
        ]);
        $otherImage = ProductImage::query()->create([
            'product_id' => $otherProduct->id,
            'image_path' => 'products/other/image.jpg',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.products.images.main', [$product, $otherImage]))
            ->assertNotFound();

        $this->assertNull($product->fresh()->main_image);
    }

    public function test_guest_and_non_admin_cannot_manage_products(): void
    {
        $this->get(route('admin.products.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_update_only_slider_visibility(): void
    {
        $admin = User::factory()->admin()->create();
        [$category, $productType] = $this->createClassification();
        $product = $this->createProduct($category, $productType);

        $this->actingAs($admin)
            ->patch(route('admin.products.slider-visibility.update', $product), [
                'show_in_slider' => true,
                'name' => 'Nom ignoré',
            ])
            ->assertRedirect();

        $product->refresh();

        $this->assertTrue($product->show_in_slider);
        $this->assertSame('Sérum hydratant', $product->name);

        $this->actingAs($admin)
            ->patch(route('admin.products.slider-visibility.update', $product), [
                'show_in_slider' => false,
            ])
            ->assertRedirect();

        $this->assertFalse($product->fresh()->show_in_slider);
    }

    public function test_slider_visibility_requires_an_administrator_and_a_boolean_value(): void
    {
        [$category, $productType] = $this->createClassification();
        $product = $this->createProduct($category, $productType);

        $this->patch(route('admin.products.slider-visibility.update', $product), ['show_in_slider' => true])
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.products.slider-visibility.update', $product), ['show_in_slider' => true])
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.products.slider-visibility.update', $product), ['show_in_slider' => 'invalid'])
            ->assertSessionHasErrors('show_in_slider');

        $this->assertFalse($product->fresh()->show_in_slider);
    }

    public function test_storefront_slider_contains_only_selected_active_products_with_images(): void
    {
        [$category, $productType] = $this->createClassification();
        $selected = $this->createProduct($category, $productType);
        $selected->update([
            'main_image' => 'https://images.example.com/selected.png',
            'show_in_slider' => true,
            'is_active' => true,
        ]);
        $notSelected = Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $productType->id,
            'name' => 'Produit non sélectionné',
            'slug' => 'produit-non-selectionne',
            'price' => '99.00',
            'stock_quantity' => 3,
            'main_image' => 'https://images.example.com/not-selected.png',
            'show_in_slider' => false,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('sliderProducts', 1)
                ->where('sliderProducts.0.id', $selected->id)
                ->where('products', fn ($products) => collect($products)->pluck('id')->contains($notSelected->id))
            );
    }

    /** @return array{Category, ProductType} */
    private function createClassification(): array
    {
        return [
            Category::query()->create([
                'name' => 'Visage',
                'slug' => 'visage',
            ]),
            ProductType::query()->create([
                'name' => 'Sérum',
                'slug' => 'serum',
            ]),
        ];
    }

    private function createProduct(Category $category, ProductType $productType): Product
    {
        return Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $productType->id,
            'name' => 'Sérum hydratant',
            'slug' => 'serum-hydratant',
            'sku' => 'SER-BASE',
            'price' => '129.90',
            'stock_quantity' => 10,
        ]);
    }
}
