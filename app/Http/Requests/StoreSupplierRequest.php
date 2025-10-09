<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'rnc' => 'nullable|string|max:50|unique:suppliers,rnc',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }
}
