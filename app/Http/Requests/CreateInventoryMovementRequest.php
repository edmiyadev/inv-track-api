<?php

namespace App\Http\Requests;

use App\Enums\MovementTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'movement_type' => [
                'required',
                Rule::in([MovementTypeEnum::Transfer->value, MovementTypeEnum::Adjustment->value]),
            ],
            'origin_warehouse_id' => 'required_if:movement_type,transfer|nullable|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'movement_type.in' => 'Movement type must be either transfer or adjustment. In/Out movements are created automatically by the system.',
        ];
    }
}
