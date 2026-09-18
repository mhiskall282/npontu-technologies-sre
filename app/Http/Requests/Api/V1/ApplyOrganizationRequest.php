<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ApplyOrganizationRequest extends FormRequest
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
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_slug' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'deployment_model' => ['nullable', 'string', 'in:shared_saas,dedicated_managed,customer_funded,customer_hosted'],
            'preferred_region' => ['nullable', 'string', 'in:af-south,us-east,eu-west'],
            'tier' => ['nullable', 'string', 'in:free,team,enterprise,customer_hosted'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
