<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:2', 'max:100'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:1500'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'الاسم مطلوب.',
            'rating.between' => 'اختاري تقييماً بين 1 و5.',
            'comment.required' => 'نص الرأي مطلوب.',
            'comment.min' => 'يجب أن يحتوي الرأي على 10 أحرف على الأقل.',
            'website.max' => 'تعذر إرسال رأيكِ.',
        ];
    }
}
