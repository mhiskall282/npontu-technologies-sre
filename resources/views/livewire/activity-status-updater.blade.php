<div>
    @if($showForm)
        <div class="min-w-[240px] bg-white border border-gray-200 rounded-lg shadow-lg p-3 space-y-2.5 text-left"
             x-data x-trap="true">
            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide flex items-center justify-between">
                <span>Update Status</span>
                <span class="text-[10px] text-gray-400">Shift Action</span>
            </p>

            <div class="flex gap-2">
                <button type="button"
                        wire:click="$set('status', 'pending')"
                        x-on:click="$wire.status = 'pending'"
                        :class="$wire.status === 'pending' ? 'bg-[#F5C518] border-[#F5C518] text-gray-900 font-bold shadow-sm' : 'border-gray-300 text-gray-600 hover:border-[#F5C518]'"
                        class="flex-1 py-1.5 text-xs font-medium rounded-md border transition-colors duration-100 {{ $status === 'pending' ? 'bg-[#F5C518] border-[#F5C518] text-gray-900 font-bold' : 'border-gray-300 text-gray-600 hover:border-[#F5C518]' }}">
                    Pending
                </button>
                <button type="button"
                        wire:click="$set('status', 'done')"
                        x-on:click="$wire.status = 'done'"
                        :class="$wire.status === 'done' ? 'bg-[#1B6B3A] border-[#1B6B3A] text-white font-bold shadow-sm' : 'border-gray-300 text-gray-600 hover:border-[#1B6B3A]'"
                        class="flex-1 py-1.5 text-xs font-medium rounded-md border transition-colors duration-100 {{ $status === 'done' ? 'bg-[#1B6B3A] border-[#1B6B3A] text-white font-bold' : 'border-gray-300 text-gray-600 hover:border-[#1B6B3A]' }}">
                    Done
                </button>
            </div>

            <div>
                <textarea wire:model="remark"
                          placeholder="Add remark or handover note (optional)..."
                          rows="2"
                          class="w-full text-xs border border-gray-300 rounded-md p-2 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] resize-none"></textarea>
                @error('remark')
                    <p class="text-xs text-[#E63946] mt-0.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- SRE Incident Escalation Toggle (FR-3 Enterprise Extension) --}}
            <div x-data="{ openEscalation: @entangle('isEscalated') || '{{ $incidentTicket }}' !== '' }" class="border-t border-gray-100 pt-2">
                <button type="button"
                        x-on:click="openEscalation = !openEscalation"
                        class="text-[11px] font-medium text-gray-500 hover:text-red-600 flex items-center justify-between w-full">
                    <span class="flex items-center gap-1">
                        <span class="text-red-500">🚨</span>
                        <span>Incident / Escalation</span>
                        @if($isEscalated || $incidentTicket)
                            <span class="px-1.5 py-0.2 text-[9px] bg-red-100 text-red-700 font-bold rounded">Active</span>
                        @endif
                    </span>
                    <svg class="w-3 h-3 transition-transform text-gray-400" :class="{ 'rotate-180': openEscalation }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="openEscalation" x-cloak class="mt-2 space-y-2 bg-red-50/50 p-2 rounded-md border border-red-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                               wire:model="isEscalated"
                               class="rounded border-red-300 text-[#E63946] focus:ring-[#E63946] h-3.5 w-3.5">
                        <span class="text-[11px] font-semibold text-red-800">Flag as Escalated Check</span>
                    </label>

                    <div>
                        <input type="text"
                               wire:model="incidentTicket"
                               placeholder="Ticket ID (e.g. INC-4091, PAY-882)"
                               class="w-full text-xs border border-red-200 rounded p-1.5 bg-white text-gray-800 placeholder-gray-400 focus:ring-1 focus:ring-[#E63946] focus:border-[#E63946]">
                        @error('incidentTicket')
                            <p class="text-[10px] text-[#E63946] mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-2 pt-0.5">
                <button type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="flex-1 py-1.5 text-xs font-semibold bg-[#1B6B3A] text-white rounded-md
                               hover:bg-[#2A8F52] transition-colors duration-150 disabled:opacity-50 flex items-center justify-center gap-1.5 shadow-sm">
                    <svg wire:loading wire:target="save" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">Save</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <button type="button"
                        wire:click="toggleForm"
                        class="px-3 py-1.5 text-xs text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-150">
                    Cancel
                </button>
            </div>
        </div>
    @else
        <button wire:click="toggleForm"
                class="px-3 py-1.5 text-xs font-medium border border-[#1B6B3A] text-[#1B6B3A] rounded-md
                       hover:bg-[#1B6B3A] hover:text-white transition-colors duration-150">
            Update
        </button>
    @endif
</div>
