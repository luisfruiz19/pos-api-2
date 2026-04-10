<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCreditSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'uuid', Rule::exists('customers', 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', Rule::exists('products', 'id')],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'El cliente es obligatorio',
            'customer_id.exists' => 'El cliente no existe',
            'items.required' => 'Debe incluir al menos un producto',
            'items.min' => 'Debe incluir al menos un producto',
            'items.*.product_id.required' => 'product_id es obligatorio',
            'items.*.product_id.exists' => 'El producto no existe',
            'items.*.cantidad.required' => 'La cantidad es obligatoria',
            'items.*.cantidad.min' => 'La cantidad mínima es 1',
        ];
    }
}
