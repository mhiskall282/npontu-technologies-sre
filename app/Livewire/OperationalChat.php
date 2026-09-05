<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Mail\MessageMentionMail;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * OperationalChat — SRE Real-Time Team Communications & Messaging Console
 *
 * Implements:
 *   - 1-on-1 Direct Private Messaging among engineers and support operators.
 *   - Shared SRE Team Shift Channels (#general-shift).
 *   - Operational Group Channels & Incident War Rooms (with privacy toggles).
 *   - Real-time message streaming with unread badges and automated read-receipt tracking.
 *   - Granular privilege checks (create_channels).
 */
#[Layout('layouts.app')]
#[Title('Operational Communications — Support Tracker')]
class OperationalChat extends Component
{
    /**
     * Currently active conversation ID.
     */
    public ?int $activeConversationId = null;

    /**
     * Composing message draft text.
     */
    public string $messageText = '';

    /**
     * Search filter for channels and direct message contacts.
     */
    public string $search = '';

    // ──────────────────────────────────────────
    // Modal State: New Conversation / Direct Chat
    // ──────────────────────────────────────────

    public bool $showNewChatModal = false;

    /**
     * Mode: 'direct' or 'group'.
     */
    public string $chatMode = 'direct';

    /**
     * Direct chat target user ID.
     */
    public ?int $directUserId = null;

    /**
     * Group channel title.
     */
    public string $channelTitle = '';

    /**
     * Group channel description.
     */
    public string $channelDescription = '';

    /**
     * Whether group channel is private.
     */
    public bool $isPrivateChannel = false;

    /**
     * Selected participant user IDs for group channel.
     *
     * @var array<int, int>
     */
    public array $selectedParticipants = [];

    /**
     * Query string persistence.
     *
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'activeConversationId' => ['as' => 'c', 'except' => null],
    ];

    /**
     * Initialize conversation state and ensure default channel exists.
     */
    public function mount(?int $c = null): void
    {
        $this->ensureDefaultChannelExists();

        if ($c && Conversation::forUser(Auth::id())->where('id', $c)->exists()) {
            $this->selectConversation($c);
        } else {
            $first = Conversation::forUser(Auth::id())->latest('updated_at')->first();
            if ($first) {
                $this->selectConversation($first->id);
            }
        }
    }

    /**
     * Ensure the baseline "#general-shift" team channel is always provisioned.
     */
    private function ensureDefaultChannelExists(): void
    {
        $general = Conversation::where('type', 'team')
            ->where('title', 'General Shift Operations')
            ->first();

        if (! $general) {
            $admin = User::where('role', 'admin')->first() ?? Auth::user();

            $general = Conversation::create([
                'type' => 'team',
                'title' => 'General Shift Operations',
                'description' => 'Shared operational channel for daily support shift handovers, announcements, and health updates.',
                'is_private' => false,
                'created_by' => $admin?->id,
            ]);

            // Enroll all active users with last_read_at initialized
            $allUserIds = User::pluck('id')->toArray();
            $syncData = [];
            foreach ($allUserIds as $uId) {
                $syncData[$uId] = ['last_read_at' => now()];
            }
            $general->participants()->sync($syncData);

            // Post welcoming operational message
            Message::create([
                'conversation_id' => $general->id,
                'sender_id' => $admin?->id ?? (int) Auth::id(),
                'body' => 'Welcome to Npontu SRE Operations Communications. Use this channel for shift handovers, incident coordination, and real-time support alerts.',
            ]);
        }
    }

    /**
     * Select active conversation and mark as read.
     */
    public function selectConversation(int $id): void
    {
        $this->activeConversationId = $id;
        $this->markActiveAsRead();
    }

    /**
     * Mark the currently active conversation as read by the current user.
     */
    public function markActiveAsRead(): void
    {
        if (! $this->activeConversationId) {
            return;
        }

        ConversationParticipant::updateOrCreate(
            [
                'conversation_id' => $this->activeConversationId,
                'user_id' => Auth::id(),
            ],
            [
                'last_read_at' => now(),
            ]
        );
    }

    /**
     * Send a new message to the active conversation.
     */
    public function sendMessage(): void
    {
        $this->validate([
            'messageText' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        if (! $this->activeConversationId) {
            return;
        }

        $conversation = Conversation::forUser(Auth::id())->findOrFail($this->activeConversationId);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => trim($this->messageText),
        ]);

        // Touch conversation updated_at for ordering
        $conversation->touch();

        // Ensure sender is enrolled as participant
        ConversationParticipant::updateOrCreate(
            [
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
            ],
            [
                'last_read_at' => now(),
            ]
        );

        // Dispatch email receipts for @mentions and @all broadcast
        $this->dispatchMentionEmails($message, $conversation);

        $this->messageText = '';
    }

    /**
     * Insert a @mention tag into the active message draft.
     */
    public function insertMention(string $mention): void
    {
        $this->messageText = trim($this->messageText.' '.$mention).' ';
    }

    /**
     * Parse message text for @mentions and dispatch email notifications.
     */
    protected function dispatchMentionEmails(Message $message, Conversation $conversation): void
    {
        $body = $message->body;
        $myId = (int) Auth::id();

        // 1. Check for @all or @everyone broadcast
        if (preg_match('/\B@(all|everyone)\b/i', $body)) {
            // Get all participants in the conversation except sender
            $recipients = $conversation->participants()->where('users.id', '!=', $myId)->get();

            foreach ($recipients as $recipient) {
                if ($recipient->email) {
                    try {
                        Mail::to($recipient->email)->send(new MessageMentionMail(
                            chatMessage: $message,
                            conversation: $conversation,
                            recipient: $recipient,
                            isBroadcast: true
                        ));
                    } catch (\Throwable $e) {
                        logger()->warning("Failed to dispatch @all broadcast email to {$recipient->email}: {$e->getMessage()}");
                    }
                }
            }

            return;
        }

        // 2. Check for individual user mentions @Name
        $colleagues = User::where('id', '!=', $myId)->get();
        $notifiedUserIds = [];

        foreach ($colleagues as $colleague) {
            $firstName = explode(' ', $colleague->name)[0];
            $pattern = '/\B@('.preg_quote($colleague->name, '/').'|'.preg_quote($firstName, '/').')\b/i';

            if (preg_match($pattern, $body) && ! in_array($colleague->id, $notifiedUserIds, true)) {
                $notifiedUserIds[] = $colleague->id;
                if ($colleague->email) {
                    try {
                        Mail::to($colleague->email)->send(new MessageMentionMail(
                            chatMessage: $message,
                            conversation: $conversation,
                            recipient: $colleague,
                            isBroadcast: false
                        ));
                    } catch (\Throwable $e) {
                        logger()->warning("Failed to dispatch @mention email to {$colleague->email}: {$e->getMessage()}");
                    }
                }
            }
        }

        // 3. If direct message (1-on-1), ALWAYS dispatch email to the other participant
        if ($conversation->type === 'direct') {
            $directRecipient = $conversation->participants()->where('users.id', '!=', $myId)->first();
            if ($directRecipient && ! in_array($directRecipient->id, $notifiedUserIds, true)) {
                $notifiedUserIds[] = $directRecipient->id;
                if ($directRecipient->email) {
                    try {
                        Mail::to($directRecipient->email)->send(new MessageMentionMail(
                            chatMessage: $message,
                            conversation: $conversation,
                            recipient: $directRecipient,
                            isBroadcast: false,
                            isDirectMessage: true
                        ));
                    } catch (\Throwable $e) {
                        logger()->warning("Failed to dispatch direct message email to {$directRecipient->email}: {$e->getMessage()}");
                    }
                }
            }
        }

        // 4. If private channel or war room, dispatch email to participants who haven't been notified yet
        if ($conversation->is_private && $conversation->type !== 'direct') {
            $participants = $conversation->participants()->where('users.id', '!=', $myId)->get();
            foreach ($participants as $participant) {
                if (! in_array($participant->id, $notifiedUserIds, true)) {
                    $notifiedUserIds[] = $participant->id;
                    if ($participant->email) {
                        try {
                            Mail::to($participant->email)->send(new MessageMentionMail(
                                chatMessage: $message,
                                conversation: $conversation,
                                recipient: $participant,
                                isBroadcast: false,
                                isDirectMessage: false
                            ));
                        } catch (\Throwable $e) {
                            logger()->warning("Failed to dispatch private war room email to {$participant->email}: {$e->getMessage()}");
                        }
                    }
                }
            }
        }
    }

    /**
     * Open Modal to start a new chat or channel.
     */
    public function openNewChatModal(string $mode = 'direct'): void
    {
        $this->chatMode = $mode;
        $this->directUserId = null;
        $this->channelTitle = '';
        $this->channelDescription = '';
        $this->isPrivateChannel = false;
        $this->selectedParticipants = [];
        $this->showNewChatModal = true;
    }

    /**
     * Close the modal.
     */
    public function closeNewChatModal(): void
    {
        $this->showNewChatModal = false;
    }

    /**
     * Start or navigate to a 1-on-1 direct message with another user.
     */
    public function startDirectChat(?int $targetUserId = null): void
    {
        $targetUserId = $targetUserId ?? $this->directUserId;

        if (! $targetUserId || $targetUserId === Auth::id()) {
            session()->flash('error', 'Please select a valid colleague to chat with.');

            return;
        }

        $targetUser = User::findOrFail($targetUserId);
        $myId = (int) Auth::id();

        // Check if direct conversation already exists between both users
        $existing = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $myId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $targetUserId))
            ->first();

        if ($existing) {
            $this->selectConversation($existing->id);
            $this->closeNewChatModal();

            return;
        }

        // Create new direct conversation
        $conv = Conversation::create([
            'type' => 'direct',
            'title' => null,
            'is_private' => true,
            'created_by' => $myId,
        ]);

        $conv->participants()->attach([
            $myId => ['last_read_at' => now()],
            $targetUserId => ['last_read_at' => null],
        ]);

        $this->selectConversation($conv->id);
        $this->closeNewChatModal();
        session()->flash('success', "Direct conversation opened with {$targetUser->name}.");
    }

    /**
     * Create a new Group Channel / Incident Room.
     */
    public function createGroupChannel(): void
    {
        if (! Auth::user()->hasPrivilege('create_channels')) {
            abort(403, 'Unauthorized to create communication channels.');
        }

        $this->validate([
            'channelTitle' => ['required', 'string', 'min:3', 'max:100'],
            'channelDescription' => ['nullable', 'string', 'max:255'],
            'isPrivateChannel' => ['boolean'],
            'selectedParticipants' => ['nullable', 'array'],
            'selectedParticipants.*' => ['integer', 'exists:users,id'],
        ]);

        $myId = (int) Auth::id();

        $conv = Conversation::create([
            'type' => 'group',
            'title' => trim($this->channelTitle),
            'description' => trim($this->channelDescription) ?: null,
            'is_private' => $this->isPrivateChannel,
            'created_by' => $myId,
        ]);

        $participants = array_unique(array_merge([$myId], $this->selectedParticipants));
        $conv->participants()->sync($participants);

        // Send opening message
        Message::create([
            'conversation_id' => $conv->id,
            'sender_id' => $myId,
            'body' => "Channel '{$conv->title}' created.",
        ]);

        $this->selectConversation($conv->id);
        $this->closeNewChatModal();
        session()->flash('success', "Group channel '{$conv->title}' established successfully.");
    }

    /**
     * Render the Livewire component.
     */
    public function render(): View
    {
        $myId = (int) Auth::id();

        // Retrieve conversations the user can access
        $conversations = Conversation::forUser($myId)
            ->with(['participants', 'latestMessage.sender', 'creator'])
            ->latest('updated_at')
            ->get();

        // If search is applied, filter conversations
        if (trim($this->search) !== '') {
            $term = mb_strtolower(trim($this->search));
            $conversations = $conversations->filter(function (Conversation $c) use ($term, $myId) {
                if ($c->type === 'direct') {
                    $other = $c->participants->firstWhere('id', '!==', $myId);

                    return $other && str_contains(mb_strtolower($other->name), $term);
                }

                return str_contains(mb_strtolower((string) $c->title), $term)
                    || str_contains(mb_strtolower((string) $c->description), $term);
            });
        }

        // Partition conversations by type
        $teamChannels = $conversations->filter(fn ($c) => $c->type === 'team');
        $groupChannels = $conversations->filter(fn ($c) => $c->type === 'group');
        $directMessages = $conversations->filter(fn ($c) => $c->type === 'direct');

        // Active conversation and messages
        $activeConversation = null;
        $messages = new Collection;

        if ($this->activeConversationId) {
            $activeConversation = Conversation::with(['participants', 'creator'])
                ->find($this->activeConversationId);

            if ($activeConversation) {
                $messages = Message::where('conversation_id', $activeConversation->id)
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->limit(100)
                    ->get();

                // Update read status whenever rendered
                $this->markActiveAsRead();
            }
        }

        // All users for starting direct chats or inviting to groups
        $colleagues = User::where('id', '!=', $myId)->orderBy('name')->get();

        return view('livewire.operational-chat', compact(
            'teamChannels',
            'groupChannels',
            'directMessages',
            'activeConversation',
            'messages',
            'colleagues'
        ));
    }
}
