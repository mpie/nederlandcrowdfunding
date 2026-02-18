<nav class="sticky top-0 z-50 nav-bar"
     x-data="{ mobileOpen: false, openDropdown: null, scrolled: false }"
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 }, { passive: true })"
     :class="scrolled && 'nav-bar--scrolled'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 md:h-[68px]">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#1a3f5c] to-[#4a7c9b] flex items-center justify-center shadow-lg shadow-[#2b5f83]/20 group-hover:shadow-[#2b5f83]/30 transition-shadow">
                    <span class="text-white font-extrabold text-sm tracking-tight">NC</span>
                </div>
                <div class="hidden sm:block leading-tight">
                    <span class="text-[15px] font-bold text-gray-900 block">Nederland Crowdfunding</span>
                    <span class="text-[10px] text-gray-400 uppercase tracking-[0.2em] font-medium">Branchevereniging</span>
                </div>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-1">
                @foreach($navbarItems->where('is_highlighted', false) as $item)
                    @if($item->children->isNotEmpty())
                        {{-- Dropdown parent --}}
                        <div class="relative" @mouseenter="openDropdown = {{ $item->id }}" @mouseleave="openDropdown = null">
                            <button class="px-4 py-2 text-[13px] font-semibold rounded-xl transition-all duration-200 flex items-center gap-1.5"
                                    :class="openDropdown === {{ $item->id }} ? 'text-[#2b5f83] bg-white/60' : 'text-gray-500 hover:text-[#2b5f83] hover:bg-white/60'">
                                {{ $item->label }}
                                <svg class="w-3.5 h-3.5 opacity-40 transition-transform duration-300" :class="openDropdown === {{ $item->id }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="openDropdown === {{ $item->id }}"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                 class="absolute left-0 top-full pt-2 z-50"
                                 x-cloak>
                                <div class="w-56 bg-white rounded-2xl shadow-xl shadow-black/10 py-2 ring-1 ring-black/[0.06]">
                                    @foreach($item->children as $child)
                                        <a href="{{ $child->resolved_url }}" target="{{ $child->target }}"
                                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:text-[#2b5f83] hover:bg-[#2b5f83]/5 transition rounded-lg mx-1">
                                            @if($child->icon)
                                                <i class="{{ $child->icon }} text-xs w-4 text-center opacity-40"></i>
                                            @endif
                                            {{ $child->label }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Regular link --}}
                        @php
                            $isActive = false;
                            if ($item->route_name) {
                                $isActive = request()->routeIs($item->route_name) || request()->routeIs($item->route_name . '.*');
                            } elseif ($item->url === '/') {
                                $isActive = request()->is('/');
                            } elseif ($item->url) {
                                $isActive = request()->is(ltrim($item->url, '/') . '*');
                            }
                        @endphp
                        <a href="{{ $item->resolved_url }}" target="{{ $item->target }}"
                           class="px-4 py-2 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ $isActive ? 'text-[#2b5f83] bg-[#2b5f83]/8 shadow-sm' : 'text-gray-500 hover:text-[#2b5f83] hover:bg-white/60' }}">
                            {{ $item->label }}
                        </a>
                    @endif
                @endforeach
            </div>

            {{-- Search + CTA + Mobile toggle --}}
            <div class="flex items-center gap-2">
                <x-search-modal class="p-2.5 rounded-xl text-gray-400 hover:text-[#2b5f83] hover:bg-white/60 transition-all duration-200" />

                @foreach($navbarItems->where('is_highlighted', true) as $cta)
                    <a href="{{ $cta->resolved_url }}" target="{{ $cta->target }}"
                       class="hidden md:inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#1a3f5c] to-[#2b5f83] hover:from-[#234d6b] hover:to-[#356f97] text-white text-[13px] font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-[#2b5f83]/25 hover:shadow-[#2b5f83]/40 hover:-translate-y-px">
                        @if($cta->icon)
                            <i class="{{ $cta->icon }} text-xs"></i>
                        @endif
                        {{ $cta->label }}
                    </a>
                @endforeach
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2.5 rounded-xl text-gray-500 hover:bg-white/60 transition">
                    <i class="fa-solid text-lg" :class="mobileOpen ? 'fa-xmark' : 'fa-bars'"></i>
                </button>
            </div>
        </div>

        {{-- Mobile nav --}}
        <div x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             x-cloak class="md:hidden pb-5 border-t border-white/20 mt-1 pt-4 space-y-1">
            @foreach($navbarItems as $item)
                @if($item->is_highlighted)
                    <a href="{{ $item->resolved_url }}" class="block mx-2 mt-3 px-4 py-2.5 text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-[#1a3f5c] to-[#2b5f83] text-center shadow-lg shadow-[#2b5f83]/20">
                        @if($item->icon)
                            <i class="{{ $item->icon }} mr-1.5 text-xs"></i>
                        @endif
                        {{ $item->label }}
                    </a>
                @elseif($item->children->isNotEmpty())
                    <div class="py-1">
                        <span class="block px-4 py-1.5 text-[11px] font-bold text-gray-400 uppercase tracking-widest">{{ $item->label }}</span>
                        @foreach($item->children as $child)
                            <a href="{{ $child->resolved_url }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-white/60 rounded-xl ml-2">{{ $child->label }}</a>
                        @endforeach
                    </div>
                @else
                    @php
                        $isActive = false;
                        if ($item->route_name) {
                            $isActive = request()->routeIs($item->route_name) || request()->routeIs($item->route_name . '.*');
                        } elseif ($item->url === '/') {
                            $isActive = request()->is('/');
                        } elseif ($item->url) {
                            $isActive = request()->is(ltrim($item->url, '/') . '*');
                        }
                    @endphp
                    <a href="{{ $item->resolved_url }}" class="block px-4 py-2.5 text-sm font-semibold rounded-xl {{ $isActive ? 'text-[#2b5f83] bg-[#2b5f83]/8' : 'text-gray-600 hover:bg-white/60' }}">{{ $item->label }}</a>
                @endif
            @endforeach
        </div>
    </div>
</nav>