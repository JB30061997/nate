<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^(?:\\+212|0)[5-7]\\d{8}$/'],
            'customer_city' => ['required', 'string', 'max:255'],
            'customer_address' => ['required', 'string', 'max:1000'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'checkout_token' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'الاسم الكامل مطلوب.',
            'customer_name.max' => 'يجب ألا يتجاوز الاسم الكامل 255 حرفاً.',
            'customer_phone.required' => 'رقم الهاتف مطلوب.',
            'customer_phone.regex' => 'أدخلي رقم هاتف مغربياً صحيحاً، مثل 0612345678.',
            'customer_city.required' => 'المدينة مطلوبة.',
            'customer_city.max' => 'يجب ألا يتجاوز اسم المدينة 255 حرفاً.',
            'customer_address.required' => 'عنوان التوصيل مطلوب.',
            'customer_address.max' => 'يجب ألا يتجاوز عنوان التوصيل 1000 حرف.',
            'customer_notes.max' => 'يجب ألا تتجاوز الملاحظات 1000 حرف.',
            'checkout_token.required' => 'انتهت جلسة الطلب. أعيدي تحميل الصفحة.',
            'checkout_token.uuid' => 'انتهت جلسة الطلب. أعيدي تحميل الصفحة.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_name' => trim((string) $this->input('customer_name')),
            'customer_phone' => preg_replace('/[\\s.-]+/', '', (string) $this->input('customer_phone')),
            'customer_city' => trim((string) $this->input('customer_city')),
            'customer_address' => trim((string) $this->input('customer_address')),
            'customer_notes' => $this->filled('customer_notes')
                ? trim((string) $this->input('customer_notes'))
                : null,
        ]);
    }
}
