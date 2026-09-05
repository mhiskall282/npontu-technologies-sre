<x-guest-layout>
    <div class="min-h-screen flex">
        {{-- Left Brand & SRE Mission Panel --}}
        <div class="hidden lg:flex lg:w-1/2 bg-[#1B6B3A] flex-col justify-between p-12 relative overflow-hidden"
             style="clip-path: polygon(0 0, 95% 0, 100% 100%, 0 100%);">
            {{-- Background Motif --}}
            <div class="absolute -right-16 -bottom-16 opacity-10 pointer-events-none">
                <svg class="w-96 h-96 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                    <polygon points="16,3 30,27 2,27"/>
                </svg>
            </div>

            <div>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                            <polygon points="16,3 30,27 2,27"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-extrabold text-white text-xl tracking-tight leading-none">Support Tracker</p>
                        <p class="text-[#F5C518] text-[10px] font-mono tracking-widest uppercase mt-0.5 font-semibold">NPONTU TECHNOLOGIES</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-black/20 border border-white/15 text-emerald-200 text-xs font-mono">
                    <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
                    <span>SRE Operations Platform &bull; v1.2</span>
                </div>

                <blockquote class="text-white">
                    <p class="text-3xl font-extrabold leading-tight tracking-tight">
                        Making you free<br>to achieve...
                    </p>
                    <p class="text-green-100 text-sm mt-3 leading-relaxed max-w-md">
                        Track every operational checkoff. Execute verifiable two-way shift handovers. Safeguard uptime with immutable compliance audit trails.
                    </p>
                </blockquote>

                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-green-700/60">
                    <div class="p-3 rounded-xl bg-black/10 border border-white/10">
                        <p class="text-2xl font-black text-[#F5C518] font-mono">100%</p>
                        <p class="text-green-200 text-xs mt-0.5">Audit Trail</p>
                    </div>
                    <div class="p-3 rounded-xl bg-black/10 border border-white/10">
                        <p class="text-2xl font-black text-[#F5C518] font-mono">&lt; 100ms</p>
                        <p class="text-green-200 text-xs mt-0.5">Telemetry Speed</p>
                    </div>
                    <div class="p-3 rounded-xl bg-black/10 border border-white/10">
                        <p class="text-2xl font-black text-[#F5C518] font-mono">0</p>
                        <p class="text-green-200 text-xs mt-0.5">Missed Handovers</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-green-300/80 relative z-10">
                <span>Authorized engineering personnel only.</span>
                <a href="{{ route('health') }}" class="text-[#F5C518] hover:underline flex items-center gap-1 font-semibold">
                    <span>Platform Status</span> &rarr;
                </a>
            </div>
        </div>

        {{-- Right Authentication Form Panel --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-[#F4F7F5]">
            <div class="w-full max-w-md">

                {{-- Mobile Brand Header --}}
                <div class="lg:hidden flex items-center justify-between gap-3 mb-6">
                    <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-[#1B6B3A] flex items-center justify-center shadow-md group-hover:bg-[#2A8F52] transition-colors">
                            <svg class="w-6 h-6 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                                <polygon points="16,3 30,27 2,27"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-extrabold text-gray-900 text-lg leading-none">Support Tracker</p>
                            <p class="text-[#1B6B3A] text-[10px] font-mono tracking-widest uppercase mt-0.5 font-bold">NPONTU TECHNOLOGIES</p>
                        </div>
                    </a>
                    <a href="{{ route('landing') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-900 flex items-center gap-1.5 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Home</span>
                    </a>
                </div>

                {{-- Card Container --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <div class="mb-6">
                        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Operator Sign-In</h1>
                        <p class="text-xs text-gray-500 mt-1">Authenticate with your operational credentials to access your shift board.</p>
                    </div>

                    {{-- Session Expired Banner --}}
                    @if (request()->has('expired') || session('expired'))
                        <div class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-[#F5C518] text-amber-900 flex items-start gap-3 shadow-2xs">
                            <span class="text-lg leading-none">⚠️</span>
                            <div>
                                <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wider">Session Expired</h4>
                                <p class="text-xs text-amber-900 mt-0.5 leading-relaxed">
                                    Your shift session timed out due to inactivity. Please sign in again to continue your active operations.
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- General Status / Error Notifications --}}
                    @if (session('status'))
                        <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs text-red-800">
                            <p class="font-bold flex items-center gap-1.5 text-red-900 mb-1">
                                <span>⚠️</span>
                                <span>Authentication Failed</span>
                            </p>
                            <ul class="list-disc list-inside space-y-0.5 text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Operational Email
                            </label>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   placeholder="operator@npontu.local"
                                   class="block w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] transition-colors @error('email') border-[#E63946] ring-1 ring-[#E63946] @enderror">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                    Password
                                </label>
                                <a href="{{ route('password.request') }}" class="text-xs text-[#1B6B3A] font-semibold hover:underline">
                                    Forgot?
                                </a>
                            </div>
                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="block w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] transition-colors @error('password') border-[#E63946] ring-1 ring-[#E63946] @enderror">
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                <input type="checkbox"
                                       name="remember"
                                       class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A]">
                                <span>Remember my terminal</span>
                            </label>
                        </div>

                        <button type="submit"
                                class="w-full bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold py-2.5 px-4 rounded-xl transition-all duration-150 text-sm shadow-md flex items-center justify-center gap-2 cursor-pointer mt-2">
                            <span>Sign in to SRE Console</span>
                            <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    {{-- ── Quick Operator Credentials Helper (Hidden on Mobile) ─────────────────────── --}}
                    <div class="hidden sm:block mt-6 pt-5 border-t border-gray-100">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">
                            Quick Operator Access (Test Accounts):
                        </p>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button"
                                    onclick="fillCredentials('admin@npontu.local', 'password')"
                                    class="px-2 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold text-center transition-colors border border-gray-200 cursor-pointer">
                                <span class="block text-[10px] text-gray-500 font-mono">Admin</span>
                                <span>Kwame</span>
                            </button>
                            <button type="button"
                                    onclick="fillCredentials('lead@npontu.local', 'password')"
                                    class="px-2 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold text-center transition-colors border border-gray-200 cursor-pointer">
                                <span class="block text-[10px] text-gray-500 font-mono">Lead</span>
                                <span>Abena</span>
                            </button>
                            <button type="button"
                                    onclick="fillCredentials('agent@npontu.local', 'password')"
                                    class="px-2 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold text-center transition-colors border border-gray-200 cursor-pointer">
                                <span class="block text-[10px] text-gray-500 font-mono">Agent</span>
                                <span>Kofi</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-500 mt-4 px-2">
                    <a href="{{ route('health') }}" class="hover:text-gray-700 font-medium flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>System Health & Diagnostics</span>
                    </a>
                    <span>SRE SLA: 99.98%</span>
                </div>

                <div class="flex items-center justify-center gap-3 text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('docs') }}" class="text-[#1B6B3A] hover:underline font-bold">Docs &amp; Guide</a>
                    <span>&bull;</span>
                    <a href="{{ route('policy.privacy') }}" class="hover:text-gray-600 transition-colors">Privacy</a>
                    <span>&bull;</span>
                    <a href="{{ route('policy.terms') }}" class="hover:text-gray-600 transition-colors">Terms</a>
                    <span>&bull;</span>
                    <a href="{{ route('landing') }}" class="hover:text-gray-600 transition-colors">Home</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillCredentials(email, password) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = password;
                emailInput.focus();
            }
        }
    </script>
</x-guest-layout>
