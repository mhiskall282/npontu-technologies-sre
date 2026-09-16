<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AcceptShiftHandoverApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canAcceptHandovers();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
