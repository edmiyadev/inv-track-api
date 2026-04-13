<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'email|unique:customers,email',
            'tax_id' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20',
        ];
    }
}
