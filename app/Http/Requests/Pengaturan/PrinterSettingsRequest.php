<?php

namespace App\Http\Requests\Pengaturan;

use Illuminate\Foundation\Http\FormRequest;

class PrinterSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode_width_mm' => ['required', 'integer', 'min:10', 'max:200'],
            'barcode_height_mm' => ['required', 'integer', 'min:10', 'max:200'],
        ];
    }
}
