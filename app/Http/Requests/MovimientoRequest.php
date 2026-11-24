<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esJugador() ?? false;
    }

    public function rules(): array
    {
        return [
            'posicion' => ['required', 'integer', 'between:0,8'],
        ];
    }

    public function messages(): array
    {
        return [
            'posicion.required' => 'Elige una casilla.',
            'posicion.integer' => 'La casilla no es válida.',
            'posicion.between' => 'La casilla debe estar entre 0 y 8.',
        ];
    }
}