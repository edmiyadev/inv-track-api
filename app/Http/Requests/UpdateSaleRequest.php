<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'sometimes|exists:customers,id',
            'warehouse_id' => 'sometimes|exists:warehouses,id',
            'notes' => 'sometimes|nullable|string',
            'date' => 'nullable|date_format:Y-m-d H:i:s',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'sometimes|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.tax_percentage' => 'sometimes|numeric|min:0|max:100',
        ];
    }
}
