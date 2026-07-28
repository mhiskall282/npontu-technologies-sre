<x-guest-layout>
    <div class="min-h-screen flex">
        {{-- Left brand panel --}}
        <div class="hidden lg:flex lg:w-1/2 bg-[#1B6B3A] flex-col justify-between p-12"
             style="clip-path: polygon(0 0, 95% 0, 100% 100%, 0 100%);">
            <div>
                <div class="flex items-center gap-3">
                    <svg class="w-10 h-10 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                        <polygon points="16,3 30,27 2,27"/>
                    </svg>
                    <div>
                        <p class="font-bold text-white text-xl tracking-tight">Support Tracker</p>
                        <p class="text-green-300 text-xs tracking-widest">NPONTU TECHNOLOGIES</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <blockquote class="text-white">
                    <p class="text-2xl font-bold leading-tight">
                        Making you free<br>to achieve...
                    </p>
                    <p class="text-green-300 text-sm mt-3">
                        Track every operational activity. Hand over every shift with confidence.
                    </p>
                </blockquote>

                <div class="grid grid-cols-3 gap-4 pt-4 border-t border-green-700">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-[#F5C518]">100%</p>
                        <p class="text-green-300 text-xs">Audit Trail</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-[#F5C518]">Real-time</p>
                        <p class="text-green-300 text-xs">Board Updates</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-[#F5C518]">0</p>
                        <p class="text-green-300 text-xs">Missed Handovers</p>
                    </div>
                </div>
            </div>

            <p class="text-green-600 text-xs">Internal operations tool. Authorised personnel only.</p>
        </div>

        {{-- Right login form --}}
        <div class="flex-1 flex items-center justify-center p-8 bg-[#F4F7F5]">
            <div class="w-full max-w-md">

                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-2 mb-8">
                    <svg class="w-8 h-8 text-[#1B6B3A]" viewBox="0 0 32 32" fill="currentColor">
                        <polygon points="16,3 30,27 2,27"/>
                    </svg>
                    <div>
                        <p class="font-bold text-gray-900">Support Tracker</p>
                        <p class="text-gray-500 text-xs tracking-widest">NPONTU TECHNOLOGIES</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                    <h1 class="text-xl font-bold text-gray-900 mb-1">Sign in to your account</h1>
                    <p class="text-sm text-gray-500 mb-6">Enter your credentials to access the team dashboard.</p>

                    @if (session('status'))
                        <div class="mb-4 text-sm font-medium text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email address
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   required autofocus autocomplete="username"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm
                                          focus:ring-[#1B6B3A] focus:border-[#1B6B3A]
                                          @error('email') border-[#E63946] @enderror">
                            @error('email')
                            <p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password
                            </label>
                            <input id="password" type="password" name="password"
                                   required autocomplete="current-password"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm
                                          focus:ring-[#1B6B3A] focus:border-[#1B6B3A]
                                          @error('password') border-[#E63946] @enderror">
                            @error('password')
                            <p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="remember"
                                       class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A]">
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}"
                               class="text-sm text-[#1B6B3A] font-semibold hover:underline">
                                Forgot password?
                            </a>
                        </div>

                        <button type="submit"
                                class="w-full bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-semibold
                                       py-2.5 px-4 rounded-lg transition-colors duration-150 text-sm shadow-sm">
                            Sign in
                        </button>
                    </form>
                </div>

                <p class="text-center text-xs text-gray-400 mt-4">
                    Contact your system administrator if you need access.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
