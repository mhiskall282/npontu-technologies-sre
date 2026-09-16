<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreConversationApiRequest;
use App\Http\Requests\Api\V1\StoreMessageApiRequest;
use App\Http\Resources\Api\V1\ConversationResource;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ConversationController extends ApiController
{
    /**
     * List conversations visible to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $conversations = Conversation::with(['participants', 'latestMessage.sender'])
            ->forUser($user->id)
            ->get()
            ->sortByDesc(function ($c) {
                return $c->latestMessage?->created_at ?? $c->created_at;
            })
            ->values();

        return $this->respondWithSuccess(
            ConversationResource::collection($conversations),
            'Conversations retrieved successfully.'
        );
    }

    /**
     * Create a new team channel, incident war room, or direct message.
     */
    public function store(StoreConversationApiRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $type = $validated['type'];
        $participantIds = $validated['participant_ids'] ?? [];

        // For direct messages, ensure creator and recipient are attached
        if ($type === 'direct') {
            if (empty($participantIds)) {
                return $this->respondValidationError([
                    'participant_ids' => ['Direct messages require a recipient participant.'],
                ]);
            }

            $recipientId = (int) $participantIds[0];

            // Check if 1-on-1 direct already exists
            $existing = Conversation::where('type', 'direct')
                ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
                ->whereHas('participants', fn ($q) => $q->where('user_id', $recipientId))
                ->first();

            if ($existing) {
                return $this->respondWithSuccess(
                    new ConversationResource($existing->load(['participants', 'latestMessage'])),
                    'Direct conversation already exists.'
                );
            }

            $title = null;
        } else {
            $title = $validated['title'] ?? 'Operations Channel';
        }

        $conversation = Conversation::create([
            'type' => $type,
            'title' => $title,
            'description' => $validated['description'] ?? null,
            'is_private' => (bool) ($validated['is_private'] ?? false),
            'created_by' => $user->id,
        ]);

        // Attach participants
        $allParticipantIds = array_unique(array_merge([$user->id], $participantIds));
        foreach ($allParticipantIds as $pId) {
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $pId,
                'last_read_at' => ($pId === $user->id) ? now() : null,
            ]);
        }

        return $this->respondCreated(
            new ConversationResource($conversation->load(['participants', 'latestMessage'])),
            'Conversation created successfully.'
        );
    }

    /**
     * Get conversation details.
     */
    public function show(Conversation $conversation, Request $request): JsonResponse
    {
        $user = $request->user();
        if ($conversation->type !== 'team' || $conversation->is_private) {
            if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
                return $this->respondForbidden('You are not a participant in this conversation.');
            }
        }

        $conversation->load(['participants', 'latestMessage.sender']);

        return $this->respondWithSuccess(
            new ConversationResource($conversation),
            'Conversation retrieved successfully.'
        );
    }

    /**
     * Get paginated messages for a conversation.
     */
    public function messages(Conversation $conversation, Request $request): JsonResponse
    {
        $user = $request->user();
        if ($conversation->type !== 'team' || $conversation->is_private) {
            if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
                return $this->respondForbidden('You are not a participant in this conversation.');
            }
        }

        $perPage = (int) $request->query('per_page', 50);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        // Update read receipt for authenticated user
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        $meta = [
            'current_page' => $messages->currentPage(),
            'last_page' => $messages->lastPage(),
            'per_page' => $messages->perPage(),
            'total' => $messages->total(),
        ];

        return $this->respondWithSuccess(
            MessageResource::collection($messages->items()),
            'Messages retrieved.',
            200,
            $meta
        );
    }

    /**
     * Post a new message with optional base64 attachment.
     */
    public function storeMessage(
        StoreMessageApiRequest $request,
        Conversation $conversation
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'] ?? '',
            'attachment_name' => $validated['attachment_name'] ?? null,
            'attachment_mime' => $validated['attachment_mime'] ?? null,
            'attachment_size' => $validated['attachment_size'] ?? null,
            'attachment_blob' => $validated['attachment_blob'] ?? null,
        ]);

        // Ensure user is in participants and update read receipt
        ConversationParticipant::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['last_read_at' => now()]
        );

        logger()->channel('state_changes')->info('message.posted', [
            'conversation_id' => $conversation->id,
            'message_id' => $message->id,
            'sender_id' => $user->id,
            'has_attachment' => $message->hasAttachment(),
        ]);

        return $this->respondCreated(
            new MessageResource($message->load('sender')),
            'Message sent successfully.'
        );
    }

    /**
     * Mark conversation messages as read.
     */
    public function markAsRead(Conversation $conversation, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        ConversationParticipant::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['last_read_at' => now()]
        );

        return $this->respondWithSuccess(
            null,
            'Conversation marked as read.'
        );
    }
}
