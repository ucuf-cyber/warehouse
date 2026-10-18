<?php

namespace App\Http\Requests\Stok;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubRackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rack_id' => ['required', 'integer', 'exists:racks,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('sub_racks', 'code')->ignore($this->route('subRak'))],
        ];
    }
}
