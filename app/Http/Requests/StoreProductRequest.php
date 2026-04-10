<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'precio_compra' => ['required', 'numeric', 'min:0.01'],
            'precio_venta' => ['required', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:2'],
            'codigo_barras' => ['nullable', 'string', 'unique:products,codigo_barras'],
            'imagen' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || $value === '') {
                    return;
                }

                if (!is_string($value)) {
                    $fail('La imagen debe ser un string en Base64.');
                    return;
                }
            }],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('imagen') && is_string($this->input('imagen'))) {
            $imagen = trim($this->input('imagen'));

            if ($imagen === '') {
                $this->merge(['imagen' => null]);
                return;
            }

            $this->merge(['imagen' => $this->normalizeBase64ImageString($imagen)]);
        }
    }

    private function normalizeBase64ImageString(string $value): string
    {
        $value = trim($value);

        if (Str::startsWith($value, 'data:')) {
            $commaPos = strpos($value, ',');
            if ($commaPos === false) {
                return $value;
            }

            $header = substr($value, 0, $commaPos + 1);
            $payload = substr($value, $commaPos + 1);
            $payload = str_replace(' ', '+', $payload);
            $payload = preg_replace('/\s+/', '', $payload) ?? $payload;

            return $header . $payload;
        }

        $value = str_replace(' ', '+', $value);
        return preg_replace('/\s+/', '', $value) ?? $value;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
