<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'product_type_id', 'name', 'slug', 'sku', 'description', 'usage_instructions', 'ingredients', 'precautions', 'price', 'compare_at_price', 'stock_quantity', 'main_image', 'is_active', 'is_featured', 'is_pack', 'show_in_slider'])]
class Product extends Model
{
    use SoftDeletes;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function packItems(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_pack_items', 'pack_product_id', 'product_id')
            ->withTrashed()
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_pack' => 'boolean',
            'show_in_slider' => 'boolean',
        ];
    }
}
