<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            'titulaire' => 'required|string|max:255',
            'nci' => 'required|string|unique:utilisateurs,nci',
            'email' => 'required|email|unique:utilisateurs,email',
            'telephone' => 'required|string|unique:utilisateurs,telephone',
            'adresse' => 'required|string|max:500',
        ];
    }
}
