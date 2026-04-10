<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'nombre')->ignore($this->category->id)],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe otra categoría con este nombre.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
        ];
    }
}
