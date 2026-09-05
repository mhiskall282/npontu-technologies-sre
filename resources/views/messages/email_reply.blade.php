<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instant Reply — SRE Operations Comms — Npontu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-full bg-[#0A140E] text-white flex flex-col justify-between antialiased selection:bg-[#F5C518] selection:text-gray-900">
    {{-- Header --}}
    <header class="border-b border-[#1A2E22] px-4 sm:px-6 py-3.5 flex items-center justify-between bg-[#0A120E]/90 backdrop-blur-sm">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
            <div class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center shadow-sm group-hover:border-[#F5C518]/50 transition-colors">
                <svg class="w-4 h-4 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                    <polygon points="16,3 30,27 2,27"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-xs sm:text-sm tracking-tight text-white block leading-none">Support Tracker</span>
                <span class="block text-[#F5C518] text-[8px] font-mono tracking-widest uppercase mt-0.5 font-semibold">NPONTU TECHNOLOGIES</span>
            </div>
        </a>

        <div class="flex items-center gap-2 text-xs text-gray-400 font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>SECURE EMAIL REPLY BRIDGE</span>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="max-w-xl w-full bg-[#0F1E15] border border-emerald-800/40 rounded-2xl shadow-2xl p-6 sm:p-8 space-y-6">
            
            {{-- Target Channel & User Info --}}
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#1B6B3A] text-[#F5C518] flex items-center justify-center font-bold text-lg shadow-sm">
                        💬
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm sm:text-base font-extrabold text-white">
                                {{ $conversation->displayTitleFor($user) }}
                            </h2>
                            <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30 uppercase">
                                {{ $conversation->type }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5 font-mono">
                            Posting as: <strong class="text-white">{{ $user->name }}</strong> ({{ $user->email }})
                        </p>
                    </div>
                </div>
            </div>

            {{-- Original Quoted Message (Context) --}}
            @if($originalMessage)
            <div class="p-4 rounded-xl bg-black/40 border border-white/10 space-y-1.5">
                <div class="flex items-center justify-between text-[11px] text-gray-400">
                    <span class="font-bold text-[#F5C518] flex items-center gap-1.5">
                        <span>Quoting {{ $originalMessage->sender?->name }}:</span>
                    </span>
                    <span class="font-mono text-[10px]">{{ $originalMessage->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs text-gray-300 italic whitespace-pre-wrap leading-relaxed">
                    "{{ $originalMessage->body }}"
                </p>
                @if($originalMessage->hasAttachment())
                    <p class="text-[11px] text-emerald-400 font-mono mt-1">
                        📎 Attached: {{ $originalMessage->attachment_name }} ({{ $originalMessage->formattedAttachmentSize() }})
                    </p>
                @endif
            </div>
            @endif

            {{-- Errors --}}
            @if($errors->any())
            <div class="p-3 rounded-xl bg-rose-950/60 border border-rose-800/40 text-xs text-rose-300">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Reply Form --}}
            <form action="{{ route('messages.email_reply.store', ['token' => $token]) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-4"
                  x-data="{ fileName: '', fileSize: '' }">
                @csrf

                <div>
                    <label for="body" class="block text-xs font-bold text-gray-300 mb-1.5">
                        Your Operational Response:
                    </label>
                    <textarea id="body"
                              name="body"
                              rows="4"
                              placeholder="Write your update, status confirmation, or handover remarks..."
                              class="w-full text-xs text-white bg-black/50 border border-white/10 rounded-xl p-3 focus:outline-none focus:border-[#F5C518] focus:ring-1 focus:ring-[#F5C518] placeholder-gray-500 resize-none leading-relaxed">{{ old('body') }}</textarea>
                </div>

                {{-- Attachment Section --}}
                <div class="p-3 rounded-xl bg-black/30 border border-white/5 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-gray-300 flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span>Attach Screenshot or PDF Document</span>
                        </label>
                        <span class="text-[10px] text-gray-500 font-mono">Max 10MB</span>
                    </div>

                    <input type="file"
                           id="attachment"
                           name="attachment"
                           accept="image/*,application/pdf"
                           @change="if ($el.files[0]) { fileName = $el.files[0].name; fileSize = Math.round($el.files[0].size / 1024) + ' KB'; }"
                           class="block w-full text-xs text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#1B6B3A] file:text-white hover:file:bg-[#2A8F52] file:cursor-pointer">

                    <template x-if="fileName">
                        <div class="flex items-center gap-2 text-xs text-emerald-400 font-mono mt-1">
                            <span>✓ Attached: <strong x-text="fileName"></strong> (<span x-text="fileSize"></span>)</span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('landing') }}" class="text-xs text-gray-400 hover:text-white transition-colors">
                        &larr; Return to Safety
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs transition-colors shadow-lg cursor-pointer">
                        <span>Post Reply to Shift Channel</span>
                        <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>

        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-[#1A2E22] px-6 py-3.5 text-center text-xs text-gray-500 bg-[#0A120E]/50">
        &copy; {{ date('Y') }} Npontu Technologies. All operations verified and recorded in immutable audit logs.
    </footer>
</body>
</html>
