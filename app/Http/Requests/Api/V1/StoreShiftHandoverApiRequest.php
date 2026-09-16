<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftHandoverApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canSignHandovers();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'date_format:Y-m-d'],
            'shift' => ['required', 'string', 'in:morning,afternoon,night'],
            'incoming_lead_id' => ['nullable', 'integer', 'exists:users,id'],
            'summary' => ['required', 'string', 'max:5000'],
            'incidents' => ['nullable', 'string', 'max:2000'],
            'pending_tasks_count' => ['nullable', 'integer', 'min:0'],
            'completed_tasks_count' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Shift handover date is required.',
            'shift.required' => 'Please select the handover shift (morning, afternoon, night).',
            'summary.required' => 'Please provide the operational handover summary briefing.',
        ];
    }
}
