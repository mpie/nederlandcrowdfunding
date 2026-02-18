<footer class="relative overflow-hidden" style="background: linear-gradient(180deg, #0d1926 0%, #0a1420 100%);">
    {{-- Decorative orb --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full opacity-[0.03]" style="background: radial-gradient(circle, rgba(74,124,155,0.8) 0%, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Main footer --}}
        <div class="py-16 grid grid-cols-1 md:grid-cols-12 gap-10">
            {{-- Brand --}}
            <div class="md:col-span-5">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#2b5f83] to-[#4a7c9b] flex items-center justify-center shadow-lg shadow-[#2b5f83]/30">
                        <span class="text-white font-extrabold text-sm">NC</span>
                    </div>
                    <div class="leading-tight">
                        <span class="text-[15px] font-bold text-white block">Nederland Crowdfunding</span>
                        <span class="text-[10px] text-gray-500 uppercase tracking-[0.2em] font-medium">Branchevereniging</span>
                    </div>
                </div>
                <p class="text-sm text-gray-400 leading-relaxed max-w-sm">
                    De branchevereniging voor beleggings- en financieringsplatformen met een ECSP vergunning actief in Nederland.
                </p>
                <a href="mailto:info@nederlandcrowdfunding.nl" class="inline-flex items-center gap-2.5 mt-6 text-sm text-[#6ba3c4] hover:text-white transition group">
                    <div class="w-8 h-8 rounded-lg bg-[#2b5f83]/15 flex items-center justify-center group-hover:bg-[#2b5f83]/25 transition">
                        <i class="fa-solid fa-envelope text-xs"></i>
                    </div>
                    info@nederlandcrowdfunding.nl
                </a>
            </div>

            {{-- Footer Pages column --}}
            @if($footerPagesItems->isNotEmpty())
                <div class="md:col-span-3">
                    <h3 class="text-[11px] font-bold text-gray-300 uppercase tracking-[0.2em] mb-5">Pagina's</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach($footerPagesItems as $item)
                            <li>
                                <a href="{{ $item->resolved_url }}" target="{{ $item->target }}"
                                   class="text-gray-400 hover:text-white transition-colors duration-200 hover:translate-x-0.5 inline-block">
                                    {{ $item->label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Footer About column --}}
            @if($footerAboutItems->isNotEmpty())
                <div class="md:col-span-4">
                    <h3 class="text-[11px] font-bold text-gray-300 uppercase tracking-[0.2em] mb-5">Over ons</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach($footerAboutItems as $item)
                            <li>
                                <a href="{{ $item->resolved_url }}" target="{{ $item->target }}"
                                   class="text-gray-400 hover:text-white transition-colors duration-200 hover:translate-x-0.5 inline-block">
                                    {{ $item->label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-white/[0.06] py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <span>&copy; {{ date('Y') }} Branchevereniging Nederland Crowdfunding</span>
            <span>Alle rechten voorbehouden</span>
        </div>
    </div>
</footer>