<?php

namespace App\Http\Requests\Pembelian;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', 'in:cash,transfer,other'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
