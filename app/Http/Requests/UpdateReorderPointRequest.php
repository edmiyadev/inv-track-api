<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReorderPointRequest extends FormRequest
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
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'reorder_point' => 'required|integer|min:0|max:10000'
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'El ID del almacén es requerido',
            'warehouse_id.exists' => 'El almacén no existe',
            'product_id.required' => 'El ID del producto es requerido',
            'product_id.exists' => 'El producto no existe',
            'reorder_point.required' => 'El punto de reorden es requerido',
            'reorder_point.min' => 'El punto de reorden debe ser mayor o igual a 0',
            'reorder_point.max' => 'El punto de reorden no puede exceder 10,000',
        ];
    }
}
