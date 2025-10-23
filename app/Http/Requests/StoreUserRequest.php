<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // adapter selon ton auth / policy
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user') ?? null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required','email','max:255',
                Rule::unique('users','email')->ignore($userId),
            ],
            'password' => $this->isMethod('post') ? 'required|string|min:8|confirmed' : 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|in:user,admin',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Le nom est obligatoire.",
            'email.required' => "L'email est obligatoire.",
            'email.email' => "L'email doit être une adresse valide.",
            'email.unique' => "Cet email est déjà utilisé.",
            'password.required' => "Le mot de passe est obligatoire.",
            'password.min' => "Le mot de passe doit contenir au moins 8 caractères.",
            'password.confirmed' => "La confirmation du mot de passe ne correspond pas.",
        ];
    }
}

