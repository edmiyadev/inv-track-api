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
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'status.enum' => 'Invalid status value. Valid values are: ' .
                implode(', ', PurchaseStatusEnum::values()),
            'warehouse_id.required_if' => 'Warehouse is required when posting a purchase',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * Add custom validation logic for status transitions
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Only validate if status is being changed
            if (! $this->has('status')) {
                return;
            }

            // Get the Purchase model (route parameter may be ID or model instance)
            $purchaseId = $this->route('purchase');
            $purchase = $purchaseId instanceof Purchase ? $purchaseId : Purchase::find($purchaseId);

            if (! $purchase) {
                return;
            }

            $currentStatus = PurchaseStatusEnum::from($purchase->status);
            $newStatus = PurchaseStatusEnum::from($this->input('status'));

            // Check if transition is valid
            if (! $currentStatus->canTransitionTo($newStatus)) {
                $validator->errors()->add(
                    'status',
                    $currentStatus->getTransitionErrorMessage($newStatus)
                );
            }

            // Validate warehouse exists when posting
            if ($newStatus === PurchaseStatusEnum::Posted) {
                $warehouseId = $this->input('warehouse_id', $purchase->warehouse_id);
                if (! $warehouseId) {
                    $validator->errors()->add(
                        'warehouse_id',
                        'Warehouse is required when posting a purchase'
                    );
                }
            }
        });
    }
}
