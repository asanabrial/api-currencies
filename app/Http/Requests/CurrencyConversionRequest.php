<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'to' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'from.required' => 'The source currency code is required.',
            'from.size' => 'The source currency code must be exactly 3 characters.',
            'from.regex' => 'The source currency code must be a valid 3-letter uppercase currency code.',
            'to.required' => 'The target currency code is required.',
            'to.size' => 'The target currency code must be exactly 3 characters.',
            'to.regex' => 'The target currency code must be a valid 3-letter uppercase currency code.',
            'amount.required' => 'The amount to convert is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'amount.max' => 'The amount cannot exceed 999,999,999.99.',
        ];
    }
}
