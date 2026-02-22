<?php

namespace App\Http\Requests\Admin\Contract;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('contract')->id;

        return [
            'contract_number'      => "required|string|unique:contracts,contract_number,{$id}",
            'client_id'            => 'required|exists:clients,id',
            'title'                => 'required|string|max:255',
            'total_value'          => 'required|numeric|min:0',
            'start_date'           => 'required|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'description'          => 'nullable|string',
    
            'status'               => 'required|in:draft,active,completed,canceled',
        ];
    }
}
