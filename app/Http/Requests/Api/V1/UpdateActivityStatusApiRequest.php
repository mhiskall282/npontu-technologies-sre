<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityStatusApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Activity|null $activity */
        $activity = $this->route('activity');

        return $activity !== null && (bool) $this->user()?->can('updateStatus', $activity);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,done'],
            'remark' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'incident_ticket' => ['nullable', 'string', 'max:50'],
            'is_escalated' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Operational check status is required.',
            'status.in' => 'Status must be either pending or done.',
            'date.date_format' => 'Shift date must follow the Y-m-d format.',
        ];
    }
}
