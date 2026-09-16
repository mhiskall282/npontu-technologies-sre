<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Activity|null $activity */
        $activity = $this->route('activity');

        return $activity !== null && (bool) $this->user()?->can('update', $activity);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:100'],
            'recurrence' => ['nullable', 'string', 'in:daily,weekly,monthly,shift'],
            'priority' => ['nullable', 'string', 'in:critical,high,medium,low'],
            'sla_time' => ['nullable', 'string', 'max:50'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Operational check title is required.',
            'assigned_to.exists' => 'The designated engineer does not exist in the active user registry.',
        ];
    }
}
