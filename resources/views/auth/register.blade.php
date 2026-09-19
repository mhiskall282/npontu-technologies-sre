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
                    <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-11 h-11 rounded-2xl bg-black/20 border border-white/20 p-1.5 shadow-lg object-contain">
                    <div>
                        <p class="font-extrabold text-white text-xl tracking-tight leading-none">Opsora SRE</p>
                        <p class="text-[#F5C518] text-[10px] font-mono tracking-widest uppercase mt-0.5 font-semibold">OPSORA SRE OPERATIONS</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-black/20 border border-white/15 text-emerald-200 text-xs font-mono">
                    <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
                    <span>Self-Service SaaS Onboarding</span>
                </div>

                <blockquote class="text-white">
                    <p class="text-3xl font-extrabold leading-tight tracking-tight">
                        Engineered for<br>Modern SRE Teams...
                    </p>
                    <p class="text-green-100 text-sm mt-3 leading-relaxed max-w-md">
                        Join an existing engineering organization with your team code, or spin up an isolated operational tenant environment in seconds.
                    </p>
                </blockquote>

                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-green-700/60">
                    <div class="p-3 rounded-xl bg-black/10 border border-white/10">
                        <p class="text-2xl font-black text-[#F5C518] font-mono">Instant</p>
                        <p class="text-green-200 text-xs mt-0.5">Workspace Setup</p>
                    </div>
                    <div class="p-3 rounded-xl bg-black/10 border border-white/10">
                        <p class="text-2xl font-black text-[#F5C518] font-mono">Isolated</p>
                        <p class="text-green-200 text-xs mt-0.5">Tenant Security</p>
                    </div>
                    <div class="p-3 rounded-xl bg-black/10 border border-white/10">
                        <p class="text-2xl font-black text-[#F5C518] font-mono">100%</p>
                        <p class="text-green-200 text-xs mt-0.5">Audited Shift SLA</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-green-300/80 relative z-10">
                <span>Enterprise grade security &bull; SOC2 Ready</span>
                <a href="{{ route('health') }}" class="text-[#F5C518] hover:underline flex items-center gap-1 font-semibold">
                    <span>Platform Status</span> &rarr;
                </a>
            </div>
        </div>

        {{-- Right Registration Form Panel --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-[#F4F7F5] overflow-y-auto">
            <div class="w-full max-w-md my-auto">

                {{-- Mobile Brand Header --}}
                <div class="lg:hidden flex items-center justify-between gap-3 mb-6">
                    <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-10 h-10 rounded-xl bg-[#1B6B3A] p-1.5 shadow-md group-hover:bg-[#2A8F52] transition-colors object-contain">
                        <div>
                            <p class="font-extrabold text-gray-900 text-lg leading-none">Opsora SRE</p>
                            <p class="text-[#1B6B3A] text-[10px] font-mono tracking-widest uppercase mt-0.5 font-bold">OPSORA SRE OPERATIONS</p>
                        </div>
                    </a>
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-900 flex items-center gap-1.5 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-2xs">
                        <span>Sign In</span>
                    </a>
                </div>

                {{-- Card Container --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <div class="mb-6">
                        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Create SRE Account</h1>
                        <p class="text-xs text-gray-500 mt-1">Get started with unified operational shift tracking and handovers.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs text-red-800">
                            <p class="font-bold flex items-center gap-1.5 text-red-900 mb-1">
                                <span>⚠️</span>
                                <span>Registration Error</span>
                            </p>
                            <ul class="list-disc list-inside space-y-0.5 text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input id="name"
                                   type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autofocus
                                   autocomplete="name"
                                   placeholder="e.g., Alex Johnson"
                                   class="block w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] transition-colors">
                        </div>

                        {{-- Operational Email --}}
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Operational Email <span class="text-red-500">*</span>
                            </label>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="username"
                                   placeholder="engineer@company.com"
                                   class="block w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] transition-colors">
                        </div>

                        {{-- Password --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input id="password"
                                       type="password"
                                       name="password"
                                       required
                                       autocomplete="new-password"
                                       placeholder="••••••••"
                                       class="block w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] transition-colors">
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Confirm <span class="text-red-500">*</span>
                                </label>
                                <input id="password_confirmation"
                                       type="password"
                                       name="password_confirmation"
                                       required
                                       autocomplete="new-password"
                                       placeholder="••••••••"
                                       class="block w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] transition-colors">
                            </div>
                        </div>

                        {{-- Organization Selection / Company Code --}}
                        <div class="pt-2 border-t border-gray-100 space-y-3">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="company_code" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                        Company Code <span class="text-gray-400 font-normal">(Optional)</span>
                                    </label>
                                    <span class="text-[10px] font-mono text-[#1B6B3A]">Join existing team</span>
                                </div>
                                <input id="company_code"
                                       type="text"
                                       name="company_code"
                                       value="{{ old('company_code') }}"
                                       placeholder="e.g. NPT-OPS-01"
                                       class="block w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm font-mono focus:ring-2 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] transition-colors">
                            </div>

                            <div class="text-center text-xs text-gray-400 font-mono">
                                &mdash; OR &mdash;
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="organization_name" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                        New Organization <span class="text-gray-400 font-normal">(Optional)</span>
                                    </label>
                                    <span class="text-[10px] font-mono text-emerald-600">Create new tenant</span>
                                </div>
                                <input id="organization_name"
                                       type="text"
                                       name="organization_name"
                                       value="{{ old('organization_name') }}"
                                       placeholder="e.g. Acme Cloud Systems"
                                       class="block w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] transition-colors">
                                <p class="text-[11px] text-gray-400 mt-1">Leave both blank to start with your private personal sandbox.</p>
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold py-2.5 px-4 rounded-xl transition-all duration-150 text-sm shadow-md flex items-center justify-center gap-2 cursor-pointer mt-4">
                            <span>Create Account &amp; Enter Platform</span>
                            <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    <div class="mt-5 pt-3 border-t border-gray-100 text-center text-xs text-gray-500">
                        Already have an operational account?
                        <a href="{{ route('login') }}" class="text-[#1B6B3A] font-bold hover:underline ml-1">Sign In</a>
                    </div>
                </div>

                <div class="flex items-center justify-center flex-wrap gap-x-3 gap-y-1.5 text-[11px] text-gray-400 mt-5">
                    <a href="{{ route('docs') }}" class="text-[#1B6B3A] hover:underline font-bold">Docs &amp; Guide</a>
                    <span>&bull;</span>
                    <a href="{{ route('health') }}" class="hover:text-[#1B6B3A] transition-colors">Platform Status</a>
                    <span>&bull;</span>
                    <a href="{{ route('policy.privacy') }}" class="hover:text-gray-600 transition-colors">Privacy</a>
                    <span>&bull;</span>
                    <a href="{{ route('policy.terms') }}" class="hover:text-gray-600 transition-colors">Terms</a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
