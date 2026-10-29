<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('product_types', 'name')->ignore($this->productTypeId())],
            'slug' => ['required', 'string', 'max:255', Rule::unique('product_types', 'slug')->ignore($this->productTypeId())],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('name')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    private function productTypeId(): int|string|null
    {
        $productType = $this->route('product_type');

        return $productType instanceof ProductType ? $productType->getKey() : $productType;
    }
}
