<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cash_register_id' => ['required', 'uuid', 'exists:cash_registers,id'],
            'metodo_pago' => ['required', Rule::in(['efectivo', 'yape', 'plin'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id', 'distinct'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'cash_register_id.required' => 'La caja registradora es obligatoria.',
            'cash_register_id.uuid' => 'El ID de la caja registradora debe ser un UUID válido.',
            'cash_register_id.exists' => 'La caja registradora seleccionada no existe.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in' => 'El método de pago debe ser efectivo, yape o plin.',
            'items.required' => 'Debe incluir al menos un producto en la venta.',
            'items.*.product_id.distinct' => 'No se pueden repetir productos en la misma venta.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.product_id.uuid' => 'El ID del producto debe ser un UUID válido.',
            'items.*.cantidad.min' => 'La cantidad de cada producto debe ser al menos 1.',
        ];
    }
}
