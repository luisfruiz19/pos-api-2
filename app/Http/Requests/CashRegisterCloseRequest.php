<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashRegisterCloseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'dinero_contado' => ['required', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
