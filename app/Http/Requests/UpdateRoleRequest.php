<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $admin = $this->user('platform_admin');
        return $admin ? $admin->hasPermission('roles.update') : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('role') ?? $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug,' . $roleId],
            'description' => ['nullable', 'string'],
            'is_system_role' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
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
            'name.required' => 'Le nom du rôle est requis.',
            'slug.required' => 'Le slug du rôle est requis.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
            'permissions.array' => 'Les permissions doivent être un tableau.',
            'permissions.*.exists' => 'Une ou plusieurs permissions sélectionnées sont invalides.',
        ];
    }
}
