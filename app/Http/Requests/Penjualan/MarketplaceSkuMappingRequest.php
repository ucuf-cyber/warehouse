<?php

namespace App\Http\Requests\Penjualan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketplaceSkuMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'marketplace' => ['required', 'in:shopee,tiktok'],
            'marketplace_sku' => [
                'required', 'string', 'max:100',
                Rule::unique('marketplace_sku_mappings')
                    ->where(fn ($q) => $q->where('marketplace', $this->input('marketplace')))
                    ->ignore($this->route('mapping')),
            ],
            'marketplace_product_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
