<?php

namespace App\Http\Requests\Stok;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('racks', 'code')->ignore($this->route('rak'))],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
