<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Conversation|null $conversation */
        $conversation = $this->route('conversation');
        if (! $conversation) {
            return false;
        }

        $user = $this->user();
        if (! $user) {
            return false;
        }

        // Public team channels are open to all authenticated users
        if ($conversation->type === 'team' && ! $conversation->is_private) {
            return true;
        }

        // Otherwise, user must be an explicit participant
        return $conversation->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'body' => ['required_without:attachment_blob', 'nullable', 'string', 'max:5000'],
            'attachment_name' => ['nullable', 'string', 'max:255'],
            'attachment_mime' => ['nullable', 'string', 'max:100'],
            'attachment_size' => ['nullable', 'integer', 'max:10485760'], // 10MB max
            'attachment_blob' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required_without' => 'Message body or an attachment is required.',
            'attachment_size.max' => 'Attachment size cannot exceed 10 MB.',
        ];
    }
}
