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
