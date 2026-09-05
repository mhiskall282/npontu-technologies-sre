<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reply Delivered — SRE Operations Comms</title>
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
    <header class="border-b border-[#1A2E22] px-6 py-3.5 flex items-center justify-between bg-[#0A120E]/90">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                    <polygon points="16,3 30,27 2,27"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-xs sm:text-sm tracking-tight text-white block leading-none">Support Tracker</span>
                <span class="block text-[#F5C518] text-[8px] sm:text-[9px] font-mono tracking-widest uppercase mt-0.5 font-semibold">NPONTU TECHNOLOGIES</span>
            </div>
        </a>
    </header>

    <main class="flex-1 flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-[#0F1E15] border border-emerald-800/40 rounded-2xl p-8 text-center space-y-5 shadow-2xl">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mx-auto text-2xl shadow-inner">
                ✓
            </div>

            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] font-mono font-bold uppercase tracking-wider mb-2">
                    MESSAGE BROADCAST TO SRE STREAM
                </div>
                <h2 class="text-xl font-black text-white">Operational Reply Delivered</h2>
                <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">
                    Your response has been sealed into the SRE audit trail and streamed to participants in <strong class="text-white">{{ $conversation->displayTitleFor($user) }}</strong>.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-black/40 border border-white/10 text-left text-xs text-gray-300 font-mono space-y-1">
                <p><span class="text-gray-500">Author:</span> <span class="text-emerald-400">{{ $user->name }}</span></p>
                <p><span class="text-gray-500">Channel:</span> <span class="text-white">#{{ $conversation->title ?? 'Direct' }}</span></p>
                <p><span class="text-gray-500">Timestamp:</span> <span class="text-gray-400">{{ now()->format('Y-m-d H:i:s \U\T\C') }}</span></p>
                @if($message->hasAttachment())
                <p><span class="text-gray-500">Attachment:</span> <span class="text-[#F5C518]">{{ $message->attachment_name }} ({{ $message->formattedAttachmentSize() }})</span></p>
                @endif
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('login') }}"
                   class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-md transition-colors">
                    Sign In to SRE Cockpit &rarr;
                </a>
                <a href="{{ route('landing') }}"
                   class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white text-xs font-semibold border border-white/10 transition-colors">
                    Back to Home
                </a>
            </div>
        </div>
    </main>

    <footer class="border-t border-[#1A2E22] px-6 py-3.5 text-center text-xs text-gray-500 bg-[#0A120E]/50">
        &copy; {{ date('Y') }} Npontu Technologies. All operations verified.
    </footer>
</body>
</html>
