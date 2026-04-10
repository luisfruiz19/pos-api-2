<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'nullable', 'uuid', 'exists:categories,id'],
            'precio_compra' => ['sometimes', 'numeric', 'min:0.01'],
            'precio_venta' => ['sometimes', 'numeric', 'min:0.01'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'stock_minimo' => ['sometimes', 'integer', 'min:2'],
            'codigo_barras' => [
                'sometimes',
                'string',
                Rule::unique('products', 'codigo_barras')->ignore($this->product->id),
            ],
            'imagen' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || $value === '') {
                    return;
                }

                if (!is_string($value)) {
                    $fail('La imagen debe ser un string en Base64.');
                    return;
                }

                if (!$this->isValidBase64Image($value)) {
                    $fail('La imagen no es un Base64 válido (jpg/png/webp, máx 2MB).');
                }
            }],
            'activo' => ['sometimes', 'boolean'],
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

    private function isValidBase64Image(string $value): bool
    {
        $base64 = $this->normalizeBase64ImageString($value);

        if (Str::startsWith($base64, 'data:')) {
            $commaPos = strpos($base64, ',');
            if ($commaPos === false) {
                return false;
            }
            $base64 = substr($base64, $commaPos + 1);
        }

        $base64 = str_replace(' ', '+', $base64);
        $base64 = preg_replace('/\s+/', '', $base64) ?? $base64;

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            return false;
        }

        if (strlen($binary) > 2 * 1024 * 1024) {
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary) ?: null;

        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
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
}
