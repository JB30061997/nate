<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreContentRequest extends FormRequest
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
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'delivery_zones' => ['nullable', 'string', 'max:5000'],
            'delivery_terms' => ['nullable', 'string', 'max:5000'],
            'about_content' => ['nullable', 'string', 'max:20000'],
            'terms_content' => ['nullable', 'string', 'max:20000'],
            'privacy_content' => ['nullable', 'string', 'max:20000'],
            'returns_content' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
