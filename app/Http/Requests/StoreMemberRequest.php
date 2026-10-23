<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'membership_date' => ['required', 'date'],
            'status' => ['required', 'in:Active,Inactive'],
        ];
    }
}