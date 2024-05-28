<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->type == "admin";
        // return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'unique:admins'],
            'phone_number' => ['required', 'unique:admins'],
            'password' => ['sometimes', 'required', 'confirmed'],
            'password_confirmation' => ['required_with:password']
        ];
    }
}
