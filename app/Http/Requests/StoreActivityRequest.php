<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Activity::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'is_pinned' => $this->boolean('is_pinned'),
            'priority' => $this->input('priority') ?: 'medium',
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'recurrence' => ['required', 'in:daily,adhoc'],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
            'sla_time' => ['nullable', 'string', 'max:10'],
            'is_pinned' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'An activity title is required.',
            'recurrence.in' => 'Recurrence must be either daily or adhoc.',
        ];
    }
}
