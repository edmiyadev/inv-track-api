<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInventoryMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'movement_type' => 'required|in:transfer,adjustment',
            'origin_warehouse_id' => 'required_if:movement_type,transfer|nullable|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'movement_type.required' => 'El tipo de movimiento es requerido',
            'movement_type.in' => 'El tipo de movimiento debe ser transfer o adjustment',
            'origin_warehouse_id.required_if' => 'El almacén origen es requerido para transferencias',
            'destination_warehouse_id.required' => 'El almacén destino es requerido',
            'items.required' => 'Debe incluir al menos un producto',
            'items.min' => 'Debe incluir al menos un producto',
        ];
    }
}
