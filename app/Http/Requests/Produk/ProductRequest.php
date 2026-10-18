<?php

namespace App\Http\Requests\Produk;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['required', 'string', 'max:30'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'harga_modal' => ['required', 'integer', 'min:0'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'harga_marketplace' => ['nullable', 'integer', 'min:0'],
            'berat' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
