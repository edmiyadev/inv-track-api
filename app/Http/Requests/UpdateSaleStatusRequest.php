<?php

namespace App\Http\Requests;

use App\Enums\SaleStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                SaleStatusEnum::Completed->value,
                SaleStatusEnum::Canceled->value
            ])],
        ];
    }
}
