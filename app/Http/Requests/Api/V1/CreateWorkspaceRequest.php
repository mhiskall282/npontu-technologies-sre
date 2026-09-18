<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'subdomain' => ['nullable', 'string', 'max:100', 'unique:workspaces,subdomain'],
            'custom_domain' => ['nullable', 'string', 'max:255', 'unique:workspaces,custom_domain'],
            'is_personal' => ['nullable', 'boolean'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'retention_days' => ['nullable', 'integer', 'min:7', 'max:3650'],
        ];
    }
}
