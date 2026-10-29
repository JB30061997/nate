<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_review_that_stays_pending(): void
    {
        $product = $this->createProduct();

        $this->post(route('reviews.store', $product), [
            'customer_name' => 'Sara',
            'rating' => 5,
            'comment' => 'Un produit très agréable à utiliser.',
            'website' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'customer_name' => 'Sara',
            'rating' => 5,
            'status' => 'pending',
        ]);
    }

    public function test_review_validation_and_honeypot_reject_invalid_submissions(): void
    {
        $product = $this->createProduct();

        $this->post(route('reviews.store', $product), [
            'customer_name' => 'S',
            'rating' => 6,
            'comment' => 'Court',
            'website' => 'spam.example',
        ])->assertSessionHasErrors(['customer_name', 'rating', 'comment', 'website']);

        $this->assertDatabaseEmpty('reviews');
    }

    public function test_storefront_exposes_only_approved_reviews_and_real_aggregates(): void
    {
        $product = $this->createProduct();
        Review::query()->create(['product_id' => $product->id, 'customer_name' => 'Nora', 'rating' => 4, 'comment' => 'Texture agréable et produit bien emballé.', 'status' => 'approved']);
        Review::query()->create(['product_id' => $product->id, 'customer_name' => 'En attente', 'rating' => 1, 'comment' => 'Cet avis ne doit pas encore être publié.', 'status' => 'pending']);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('products.0.reviews_count', 1)
                ->where('products.0.rating_average', 4)
                ->has('products.0.reviews', 1)
                ->where('products.0.reviews.0.customer_name', 'Nora')
            );
    }

    public function test_only_administrator_can_moderate_reviews(): void
    {
        $review = $this->createProduct()->reviews()->create(['customer_name' => 'Imane', 'rating' => 5, 'comment' => 'Je recommande ce produit sans hésiter.', 'status' => 'pending']);

        $this->get(route('admin.reviews.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->patch(route('admin.reviews.update', $review), ['status' => 'approved'])->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->patch(route('admin.reviews.update', $review), ['status' => 'approved'])->assertRedirect();

        $this->assertSame('approved', $review->fresh()->status);
    }

    public function test_reviews_are_removed_with_their_product(): void
    {
        $product = $this->createProduct();
        $product->reviews()->create(['customer_name' => 'Lina', 'rating' => 5, 'comment' => 'Très satisfaite de cette découverte.', 'status' => 'approved']);

        $product->forceDelete();

        $this->assertDatabaseEmpty('reviews');
    }

    private function createProduct(): Product
    {
        $category = Category::query()->create(['name' => 'Visage', 'slug' => 'visage']);
        $type = ProductType::query()->create(['name' => 'Sérum', 'slug' => 'serum']);

        return Product::query()->create([
            'category_id' => $category->id,
            'product_type_id' => $type->id,
            'name' => 'Sérum éclat - سيروم الإشراق',
            'slug' => 'serum-eclat',
            'price' => '149.00',
            'stock_quantity' => 5,
            'is_active' => true,
        ]);
    }
}
