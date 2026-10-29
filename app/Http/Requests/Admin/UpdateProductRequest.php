<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'product_type_id' => ['required', 'integer', Rule::exists('product_types', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->productId())],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($this->productId())],
            'description' => ['nullable', 'string'],
            'usage_instructions' => ['nullable', 'string'],
            'ingredients' => ['nullable', 'string'],
            'precautions' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'gte:price', 'max:99999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'is_pack' => ['required', 'boolean'],
            'pack_items' => [Rule::excludeIf(! $this->boolean('is_pack')), Rule::requiredIf($this->boolean('is_pack')), 'array', 'min:1'],
            'pack_items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->where(fn ($query) => $query->where('is_pack', false)->where('id', '!=', $this->productId()))],
            'pack_items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'main_image_url' => ['nullable', 'url:https', 'max:2048'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('name')),
            'sku' => $this->filled('sku') ? $this->input('sku') : null,
            'compare_at_price' => $this->filled('compare_at_price') ? $this->input('compare_at_price') : null,
            'main_image_url' => $this->filled('main_image_url') ? $this->input('main_image_url') : null,
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'is_pack' => $this->boolean('is_pack'),
        ]);
    }

    private function productId(): int|string|null
    {
        $product = $this->route('product');

        return $product instanceof Product ? $product->getKey() : $product;
    }
}
