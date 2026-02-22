<?php

namespace App\Http\Requests\Admin\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')->id ?? null;

        return [
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|unique:clients,email,' . $clientId,
            'phone'           => 'nullable|string|max:50|unique:clients,phone,' . $clientId,
            'address'         => 'nullable|string',
            'tax_number'      => 'nullable|string|max:100',
            'company_name'    => 'nullable|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'notes'           => 'nullable|string',
        ];
    }
}
