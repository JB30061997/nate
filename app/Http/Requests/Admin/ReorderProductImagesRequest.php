<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderProductImagesRequest extends FormRequest
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
            'image_ids' => ['required', 'array'],
            'image_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('product_images', 'id')->where('product_id', $this->productId()),
            ],
        ];
    }

    private function productId(): int|string|null
    {
        $product = $this->route('product');

        return $product instanceof Product ? $product->getKey() : $product;
    }
}
