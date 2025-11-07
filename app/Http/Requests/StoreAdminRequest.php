<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $admin = $this->user('platform_admin');
        return $admin ? $admin->hasPermission('admins.create') : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'unique:platform_admins,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:platform_admins,email'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            // Le mot de passe est généré automatiquement, donc pas besoin de validation
            'status' => ['required', 'in:active,inactive,suspended'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Le nom d\'utilisateur est requis.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'status.required' => 'Le statut est requis.',
            'status.in' => 'Le statut doit être actif, inactif ou suspendu.',
            'roles.array' => 'Les rôles doivent être un tableau.',
            'roles.*.exists' => 'Un ou plusieurs rôles sélectionnés sont invalides.',
        ];
    }
}
