<?php

namespace App\Http\Requests\Admin\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // يمكن تعديلها لاحقًا حسب الصلاحيات
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|unique:clients,email',
            'phone'           => 'nullable|string|max:50|unique:clients,phone',
            'address'         => 'nullable|string',
            'tax_number'      => 'nullable|string|max:100',
            'company_name'    => 'nullable|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'notes'           => 'nullable|string',
        ];
    }
}
