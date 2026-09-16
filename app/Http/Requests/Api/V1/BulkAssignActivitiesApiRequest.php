<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkAssignActivitiesApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canAssignTasks();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'activity_ids' => ['required', 'array', 'min:1'],
            'activity_ids.*' => ['integer', 'exists:activities,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'activity_ids.required' => 'Please select at least one check to delegate.',
            'activity_ids.min' => 'Please select at least one check to delegate.',
            'assigned_to.exists' => 'The selected engineer does not exist in the active user registry.',
        ];
    }
}
