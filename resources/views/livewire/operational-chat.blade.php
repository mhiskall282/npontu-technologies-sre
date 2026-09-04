<div class="h-[calc(100vh-140px)] min-h-[600px] flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden"
     x-data="{
        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
     }"
     x-init="$nextTick(() => scrollToBottom())"
     @message-sent.window="$nextTick(() => scrollToBottom())">

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div class="p-3 bg-emerald-50 border-b border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
    </div>
    @endif
    @if(session('error'))
    <div class="p-3 bg-red-50 border-b border-red-200 text-xs font-semibold text-red-900 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-red-600 hover:text-red-900">&times;</button>
    </div>
    @endif

    <div class="flex-1 flex overflow-hidden">
        {{-- ── Left Sidebar: Channels & Direct Messages ────────────── --}}
        <aside class="w-80 border-r border-gray-200 flex flex-col bg-gray-50/60 flex-shrink-0">
            {{-- Header & Search --}}
            <div class="p-4 border-b border-gray-200 bg-white">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#12492A] to-[#1B6B3A] text-[#F5C518] flex items-center justify-center font-bold text-sm shadow-xs">
                            💬
                        </div>
                        <div>
                            <h2 class="text-sm font-extrabold text-gray-900 leading-tight">SRE Operations Comms</h2>
                            <p class="text-[11px] text-gray-500">Live Team Messaging Pipeline</p>
                        </div>
                    </div>

                    {{-- New Chat Dropdown / Action --}}
                    <div class="relative" x-data="{ open: false }">
                        <button type="button"
                                @click="open = !open"
                                class="p-1.5 rounded-lg bg-[#1B6B3A] text-white hover:bg-[#15532D] transition-colors shadow-xs"
                                title="Start conversation">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>

                        <div x-show="open"
                             @click.outside="open = false"
                             x-cloak
                             class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-200 py-1.5 z-30 text-xs text-gray-700 animate-scale-in">
                            <button type="button"
                                    wire:click="openNewChatModal('direct')"
                                    @click="open = false"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 font-medium">
                                <span class="text-[#1B6B3A]">👤</span>
                                <span>1-on-1 Direct Message</span>
                            </button>
                            @if(auth()->user()->hasPrivilege('create_channels'))
                            <button type="button"
                                    wire:click="openNewChatModal('group')"
                                    @click="open = false"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 font-medium">
                                <span class="text-[#F5C518]">🛡️</span>
                                <span>New Ops Group / War Room</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Quick search --}}
                <div class="relative">
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Filter channels or colleagues..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs bg-gray-100 border border-transparent rounded-lg focus:bg-white focus:border-[#1B6B3A] focus:ring-1 focus:ring-[#1B6B3A]">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Channels & Conversations List --}}
            <div class="flex-1 overflow-y-auto p-2 space-y-4">
                {{-- Team Channels Section --}}
                <div>
                    <div class="px-2 mb-1.5 flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        <span>Team Shift Channels</span>
                        <span class="text-[10px] text-gray-400">{{ $teamChannels->count() }}</span>
                    </div>
                    <div class="space-y-0.5">
                        @forelse($teamChannels as $channel)
                            @php
                                $unread = $channel->unreadCountFor(auth()->id());
                                $isActive = $activeConversationId === $channel->id;
                            @endphp
                            <button type="button"
                                    wire:click="selectConversation({{ $channel->id }})"
                                    class="w-full text-left px-2.5 py-2 rounded-lg text-xs transition-colors flex items-center justify-between group {{ $isActive ? 'bg-[#1B6B3A] text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-200/70' }}">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="text-sm {{ $isActive ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-[#1B6B3A]' }}">#</span>
                                    <span class="truncate">{{ $channel->title }}</span>
                                </div>
                                @if($unread > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#E63946] text-white">
                                        {{ $unread }}
                                    </span>
                                @endif
                            </button>
                        @empty
                            <p class="text-[11px] text-gray-400 px-2 italic">No team channels</p>
                        @endforelse
                    </div>
                </div>

                {{-- Group Channels / War Rooms Section --}}
                <div>
                    <div class="px-2 mb-1.5 flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        <span>Ops Groups & Rooms</span>
                        <span class="text-[10px] text-gray-400">{{ $groupChannels->count() }}</span>
                    </div>
                    <div class="space-y-0.5">
                        @forelse($groupChannels as $channel)
                            @php
                                $unread = $channel->unreadCountFor(auth()->id());
                                $isActive = $activeConversationId === $channel->id;
                            @endphp
                            <button type="button"
                                    wire:click="selectConversation({{ $channel->id }})"
                                    class="w-full text-left px-2.5 py-2 rounded-lg text-xs transition-colors flex items-center justify-between group {{ $isActive ? 'bg-[#1B6B3A] text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-200/70' }}">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="text-xs">{{ $channel->is_private ? '🔒' : '📢' }}</span>
                                    <span class="truncate">{{ $channel->title }}</span>
                                </div>
                                @if($unread > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#E63946] text-white">
                                        {{ $unread }}
                                    </span>
                                @endif
                            </button>
                        @empty
                            <p class="text-[11px] text-gray-400 px-2 italic">No group rooms</p>
                        @endforelse
                    </div>
                </div>

                {{-- 1-on-1 Direct Messages Section --}}
                <div>
                    <div class="px-2 mb-1.5 flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        <span>Direct Messages</span>
                        <button type="button"
                                wire:click="openNewChatModal('direct')"
                                class="text-[10px] text-[#1B6B3A] hover:underline font-semibold lowercase">
                            + new
                        </button>
                    </div>
                    <div class="space-y-0.5">
                        @forelse($directMessages as $dm)
                            @php
                                $otherUser = $dm->participants->firstWhere('id', '!==', auth()->id());
                                $unread = $dm->unreadCountFor(auth()->id());
                                $isActive = $activeConversationId === $dm->id;
                            @endphp
                            <button type="button"
                                    wire:click="selectConversation({{ $dm->id }})"
                                    class="w-full text-left px-2.5 py-2 rounded-lg text-xs transition-colors flex items-center justify-between group {{ $isActive ? 'bg-[#1B6B3A] text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-200/70' }}">
                                <div class="flex items-center gap-2 truncate">
                                    <div class="w-6 h-6 rounded-full flex-shrink-0 {{ $isActive ? 'bg-white text-[#1B6B3A]' : 'bg-gray-200 text-gray-700' }} flex items-center justify-center font-bold text-[10px]">
                                        {{ strtoupper(substr($otherUser?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="truncate">
                                        <p class="truncate leading-none">{{ $otherUser?->name ?? 'Colleague' }}</p>
                                        @if($otherUser?->grade)
                                            <span class="text-[9px] font-mono {{ $isActive ? 'text-green-100' : 'text-gray-400' }}">{{ $otherUser->grade }} • {{ $otherUser->department ?? 'SRE' }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($unread > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#E63946] text-white">
                                        {{ $unread }}
                                    </span>
                                @endif
                            </button>
                        @empty
                            <p class="text-[11px] text-gray-400 px-2 italic">No active direct chats</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </aside>

        {{-- ── Right Main Area: Active Chat Stream ─────────────────── --}}
        <main class="flex-1 flex flex-col bg-white overflow-hidden">
            @if($activeConversation)
                {{-- Chat Header --}}
                <div class="px-6 py-3.5 border-b border-gray-200 flex items-center justify-between bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#12492A] to-[#1B6B3A] text-[#F5C518] flex items-center justify-center font-bold text-base shadow-xs">
                            @if($activeConversation->type === 'direct')
                                👤
                            @elseif($activeConversation->is_private)
                                🔒
                            @else
                                🛡️
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900">
                                    {{ $activeConversation->displayTitleFor(auth()->user()) }}
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $activeConversation->type === 'direct' ? 'bg-blue-100 text-blue-800' : ($activeConversation->is_private ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ ucfirst($activeConversation->type) }} {{ $activeConversation->is_private ? '(Private)' : '' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($activeConversation->description)
                                    {{ $activeConversation->description }}
                                @elseif($activeConversation->type === 'direct')
                                    @php $partner = $activeConversation->participants->firstWhere('id', '!==', auth()->id()); @endphp
                                    @if($partner)
                                        Grade {{ $partner->grade ?? 'L2' }} • {{ $partner->designation ?? 'Support Operator' }} ({{ $partner->department ?? 'Operations' }})
                                    @endif
                                @else
                                    {{ $activeConversation->participants->count() }} active participant(s)
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-gray-400 font-mono flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Channel Sync
                        </span>
                    </div>
                </div>

                {{-- Messages Stream with Polling --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50"
                     x-ref="messagesContainer"
                     wire:poll.4000ms="markActiveAsRead">
                    @forelse($messages as $msg)
                        @php
                            $isMine = $msg->sender_id === auth()->id();
                        @endphp
                        <div class="flex gap-3 {{ $isMine ? 'justify-end' : 'justify-start' }}">
                            @if(!$isMine)
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-700 to-gray-900 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs">
                                    {{ strtoupper(substr($msg->sender?->name ?? '?', 0, 1)) }}
                                </div>
                            @endif

                            <div class="max-w-[70%] {{ $isMine ? 'items-end' : 'items-start' }} flex flex-col">
                                <div class="flex items-center gap-2 mb-1 px-1">
                                    <span class="text-xs font-bold {{ $isMine ? 'text-[#1B6B3A]' : 'text-gray-900' }}">
                                        {{ $isMine ? 'You' : $msg->sender?->name }}
                                    </span>
                                    @if($msg->sender?->grade)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-gray-200 text-gray-700">
                                            {{ $msg->sender->grade }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-gray-400 font-mono">
                                        {{ $msg->created_at->format('H:i') }}
                                    </span>
                                </div>

                                <div class="px-4 py-2.5 rounded-2xl text-xs leading-relaxed shadow-xs {{ $isMine ? 'bg-[#1B6B3A] text-white rounded-br-none' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-none' }}">
                                    @php
                                        $bodyText = e($msg->body);
                                        $bodyText = preg_replace(
                                            '/\B@(all|everyone)\b/i',
                                            '<span class="inline-flex items-center px-1.5 py-0.2 rounded font-extrabold bg-[#F5C518] text-gray-950 text-[10px] shadow-2xs">📢 @$1</span>',
                                            $bodyText
                                        );
                                        $bodyText = preg_replace(
                                            '/\B@([A-Za-z0-9_\-\.]+)/',
                                            '<span class="inline-flex items-center px-1.5 py-0.2 rounded font-bold bg-emerald-100 text-emerald-900 text-[10px]">@$1</span>',
                                            $bodyText
                                        );
                                    @endphp
                                    <p class="whitespace-pre-wrap break-words">{!! $bodyText !!}</p>
                                </div>
                            </div>

                            @if($isMine)
                                <div class="w-8 h-8 rounded-full bg-[#1B6B3A] text-[#F5C518] flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center p-8 text-gray-400">
                            <span class="text-4xl mb-2">💬</span>
                            <p class="text-sm font-semibold text-gray-600">No operational messages yet in this channel.</p>
                            <p class="text-xs text-gray-400 mt-1">Post an update or check-in to begin communications.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Message Input Box & @Mention Autocomplete Toolbar --}}
                <div class="p-4 border-t border-gray-200 bg-white space-y-2">
                    {{-- Mention Helper Chips --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs text-gray-500">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1 flex-shrink-0">
                            <span>@ Tag:</span>
                        </span>
                        <button type="button"
                                wire:click="insertMention('@all')"
                                class="px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-[#F5C518]/20 text-amber-900 border border-[#F5C518]/40 hover:bg-[#F5C518] hover:text-gray-900 transition-colors flex items-center gap-1 flex-shrink-0">
                            <span>📢</span> @all (Broadcast)
                        </button>
                        @foreach($colleagues->take(8) as $c)
                        <button type="button"
                                wire:click="insertMention('@' . {{ json_encode($c->name) }})"
                                class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-[#1B6B3A] border border-emerald-200 hover:bg-[#1B6B3A] hover:text-white transition-colors flex items-center gap-1 flex-shrink-0">
                            <span>@</span>{{ $c->name }} <span class="text-[9px] opacity-70 font-mono">({{ $c->grade ?? 'L2' }})</span>
                        </button>
                        @endforeach
                    </div>

                    <form wire:submit="sendMessage"
                          @submit="$nextTick(() => scrollToBottom())"
                          class="flex items-end gap-2">
                        <div class="flex-1 relative">
                            <textarea wire:model="messageText"
                                      rows="2"
                                      placeholder="Type your operational update or @tag colleagues (Press Shift+Enter for new line)..."
                                      @keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage(); $nextTick(() => scrollToBottom()); }"
                                      class="w-full text-xs rounded-xl border-gray-300 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] resize-none"></textarea>
                            @error('messageText') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2.5 bg-[#1B6B3A] hover:bg-[#15532D] text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-1.5 flex-shrink-0">
                            <svg wire:loading class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Send</span>
                            <svg class="w-3.5 h-3.5 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>
            @else
                <div class="h-full flex flex-col items-center justify-center text-center p-8 text-gray-400">
                    <span class="text-5xl mb-3">📡</span>
                    <h3 class="text-base font-bold text-gray-700">Select a Communication Channel</h3>
                    <p class="text-xs text-gray-500 max-w-sm mt-1">Choose a team shift channel or colleague from the left drawer, or start a new direct message.</p>
                </div>
            @endif
        </main>
    </div>

    {{-- ── Modal: New Chat / Group Channel ─────────────────────── --}}
    @if($showNewChatModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 text-left border border-gray-200 animate-scale-in"
             x-data x-trap="true">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-lg bg-green-50 text-[#1B6B3A]">
                        💬
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">
                            {{ $chatMode === 'direct' ? 'Start 1-on-1 Direct Chat' : 'Create Operational Group Channel' }}
                        </h3>
                        <p class="text-xs text-gray-500">Connect with support operators and engineering leads</p>
                    </div>
                </div>
                <button type="button" wire:click="closeNewChatModal" class="text-gray-400 hover:text-gray-600">
                    &times;
                </button>
            </div>

            {{-- Mode Switcher --}}
            <div class="flex border-b border-gray-200 mb-4">
                <button type="button"
                        wire:click="$set('chatMode', 'direct')"
                        class="pb-2 px-4 text-xs font-bold border-b-2 transition-colors {{ $chatMode === 'direct' ? 'border-[#1B6B3A] text-[#1B6B3A]' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    1-on-1 Direct Chat
                </button>
                @if(auth()->user()->hasPrivilege('create_channels'))
                <button type="button"
                        wire:click="$set('chatMode', 'group')"
                        class="pb-2 px-4 text-xs font-bold border-b-2 transition-colors {{ $chatMode === 'group' ? 'border-[#1B6B3A] text-[#1B6B3A]' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Ops Group / War Room
                </button>
                @endif
            </div>

            @if($chatMode === 'direct')
                {{-- Direct Message Form --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Select Colleague</label>
                        <div class="max-h-60 overflow-y-auto space-y-1.5 border border-gray-200 rounded-xl p-2 bg-gray-50/50">
                            @forelse($colleagues as $colleague)
                            <div wire:click="startDirectChat({{ $colleague->id }})"
                                 class="p-2.5 rounded-lg bg-white border border-gray-200 hover:border-[#1B6B3A] hover:bg-emerald-50/30 cursor-pointer flex items-center justify-between transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($colleague->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900 leading-tight">{{ $colleague->name }}</p>
                                        <p class="text-[10px] text-gray-500">{{ $colleague->role }} • {{ $colleague->grade ?? 'L2' }} ({{ $colleague->department ?? 'Operations' }})</p>
                                    </div>
                                </div>
                                <span class="text-xs text-[#1B6B3A] font-bold">Chat &rarr;</span>
                            </div>
                            @empty
                            <p class="text-xs text-gray-400 text-center py-4">No other colleagues registered yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                {{-- Group Channel Form --}}
                <form wire:submit="createGroupChannel" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                            Channel / Room Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model="channelTitle"
                               placeholder="e.g. PaySwitch Incident Room or Night Shift SREs"
                               class="w-full text-xs rounded-lg border-gray-300 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                        @error('channelTitle') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Description (Optional)</label>
                        <input type="text"
                               wire:model="channelDescription"
                               placeholder="Brief objective of this operational communications room"
                               class="w-full text-xs rounded-lg border-gray-300 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                        @error('channelDescription') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox"
                                   wire:model="isPrivateChannel"
                                   class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A]">
                            <span class="text-xs font-semibold text-gray-800">
                                Make this Channel Private (Invite-Only)
                            </span>
                        </label>
                        <p class="text-[11px] text-gray-500 ml-6 mt-0.5">When checked, only invited members can view and participate.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Invite Team Members</label>
                        <div class="max-h-40 overflow-y-auto space-y-1 border border-gray-200 rounded-lg p-2 bg-gray-50">
                            @foreach($colleagues as $c)
                            <label class="flex items-center gap-2 p-1 hover:bg-white rounded cursor-pointer text-xs text-gray-700">
                                <input type="checkbox"
                                       value="{{ $c->id }}"
                                       wire:model="selectedParticipants"
                                       class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A]">
                                <span>{{ $c->name }}</span>
                                <span class="text-[10px] text-gray-400">({{ $c->grade ?? 'L2' }} - {{ $c->role }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button"
                                wire:click="closeNewChatModal"
                                class="px-4 py-2 text-xs font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-xs font-bold bg-[#1B6B3A] text-white rounded-lg hover:bg-[#15532D] transition-colors shadow-sm">
                            Create Channel
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    @endif
</div>
