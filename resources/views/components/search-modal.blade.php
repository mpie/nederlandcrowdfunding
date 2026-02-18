{{-- Trigger --}}
<button x-data @click="$dispatch('open-search')" {{ $attributes->merge(['type' => 'button']) }}>
    @if($slot->isNotEmpty())
        {{ $slot }}
    @else
        <i class="fa-solid fa-magnifying-glass text-sm"></i>
    @endif
</button>

@once
@push('modals')
<div x-data="postSearch" @open-search.window="open()" @keydown.window.prevent.ctrl.k="open()" @keydown.window.prevent.meta.k="open()">
    {{-- Backdrop --}}
    <div x-show="isOpen" @click="close()" class="fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm" x-transition.opacity x-cloak></div>

    {{-- Panel --}}
    <div x-show="isOpen" @keydown.escape.window="close()" x-cloak class="fixed z-[101] overflow-hidden bg-white rounded-2xl shadow-2xl ring-1 ring-black/[0.06]" style="top: 16vh; left: 50%; transform: translateX(-50%); width: min(540px, calc(100% - 2rem));">

        {{-- Header with search input --}}
        <div class="relative">
            <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
            <div class="flex items-center gap-3 px-5 py-1">
                <div class="w-8 h-8 rounded-lg bg-[#2b5f83]/[0.07] flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-magnifying-glass text-xs" :class="loading ? 'text-[#2b5f83] animate-pulse' : 'text-[#2b5f83]/50'"></i>
                </div>
                <input type="text"
                       x-ref="input"
                       x-model.debounce.250ms="query"
                       placeholder="Zoek in berichten..."
                       @keydown.escape="close()"
                       @keydown.arrow-down.prevent="moveDown()"
                       @keydown.arrow-up.prevent="moveUp()"
                       @keydown.enter.prevent="goToSelected()"
                       class="flex-1 py-4 text-[15px] text-gray-900 placeholder-gray-400 bg-transparent outline-none">
                <button @click="close()" class="px-2 py-1 text-[11px] font-medium text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg border border-gray-200 transition-colors">
                    ESC
                </button>
            </div>
        </div>

        {{-- Results --}}
        <div class="max-h-[380px] overflow-y-auto overscroll-contain">
            {{-- Loading --}}
            <div x-show="loading" class="py-12 text-center">
                <div class="w-10 h-10 rounded-xl bg-[#2b5f83]/[0.06] flex items-center justify-center mx-auto mb-3">
                    <svg class="animate-spin w-4 h-4 text-[#2b5f83]" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </div>
                <p class="text-xs text-gray-400">Zoeken...</p>
            </div>

            {{-- Results list --}}
            <template x-if="!loading && results.length > 0">
                <div class="py-2 px-2">
                    <template x-for="(result, index) in results" :key="index">
                        <a :href="result.url"
                           class="flex items-center gap-3.5 px-3 py-3 rounded-xl transition-all duration-150"
                           :class="index === activeIndex ? 'bg-gradient-to-r from-[#2b5f83]/[0.06] to-[#2b5f83]/[0.02]' : 'hover:bg-gray-50'"
                           @mouseenter="activeIndex = index">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                 :class="index === activeIndex ? 'bg-[#2b5f83]/10 text-[#2b5f83]' : 'bg-gray-100 text-gray-400'">
                                <i class="fa-regular fa-newspaper text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate text-sm" x-text="result.title"></p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[11px] text-gray-400" x-text="result.date"></span>
                                    <template x-if="result.excerpt">
                                        <span class="text-[11px] text-gray-300 truncate">&middot; <span x-text="result.excerpt"></span></span>
                                    </template>
                                </div>
                            </div>
                            <i x-show="index === activeIndex" class="fa-solid fa-arrow-right text-xs text-[#2b5f83]/40 shrink-0"></i>
                        </a>
                    </template>
                </div>
            </template>

            {{-- No results --}}
            <div x-show="!loading && query.length >= 2 && results.length === 0" class="py-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-regular fa-face-meh text-gray-300 text-lg"></i>
                </div>
                <p class="text-sm font-medium text-gray-500">Geen resultaten</p>
                <p class="text-xs text-gray-400 mt-1">Probeer een andere zoekterm</p>
            </div>

            {{-- Hint --}}
            <div x-show="!loading && query.length < 2" class="py-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-magnifying-glass text-gray-300 text-lg"></i>
                </div>
                <p class="text-sm font-medium text-gray-500">Zoek in berichten</p>
                <p class="text-xs text-gray-400 mt-1">Typ minstens 2 karakters</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="relative">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
            <div class="flex items-center justify-between px-5 py-2.5">
                <div class="hidden sm:flex items-center gap-3 text-[11px] text-gray-400">
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white rounded-md border border-gray-200 font-mono text-[10px] shadow-sm">&uarr;&darr;</kbd> navigeer</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white rounded-md border border-gray-200 font-mono text-[10px] shadow-sm">&crarr;</kbd> open</span>
                </div>
                <a href="{{ route('posts.index') }}" class="text-xs font-semibold text-[#2b5f83] hover:underline">Alle berichten &rarr;</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('postSearch', () => ({
        isOpen: false,
        query: '',
        results: [],
        loading: false,
        activeIndex: 0,
        _abort: null,

        open() {
            this.isOpen = true;
            this.$nextTick(() => this.$refs.input?.focus());
        },

        close() {
            this.isOpen = false;
            this.query = '';
            this.results = [];
            this.activeIndex = 0;
        },

        moveDown() {
            if (this.activeIndex < this.results.length - 1) this.activeIndex++;
        },

        moveUp() {
            if (this.activeIndex > 0) this.activeIndex--;
        },

        goToSelected() {
            if (this.results[this.activeIndex]) {
                window.location.href = this.results[this.activeIndex].url;
            }
        },

        init() {
            this.$watch('query', (val) => {
                if (this._abort) this._abort.abort();
                if (val.length < 2) { this.results = []; this.loading = false; return; }

                this.loading = true;
                this.activeIndex = 0;
                this._abort = new AbortController();

                fetch(`{{ route('posts.search') }}?q=${encodeURIComponent(val)}`, { signal: this._abort.signal })
                    .then(r => r.json())
                    .then(data => { this.results = data; this.loading = false; })
                    .catch(e => { if (e.name !== 'AbortError') this.loading = false; });
            });
        }
    }));
});
</script>
@endpush
@endonce