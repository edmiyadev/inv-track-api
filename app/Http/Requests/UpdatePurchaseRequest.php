<?php

namespace App\Http\Requests;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
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
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'warehouse_id' => 'sometimes|nullable|exists:warehouses,id',
            'notes' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:draft,posted,canceled',
            'date' => 'nullable|date_format:Y-m-d H:i:s',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.tax_id' => 'required_with:items|exists:taxes,id',
        ];
    }
}
