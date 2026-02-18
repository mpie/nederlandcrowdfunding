@props(['title' => '', 'href' => null, 'icon' => null])

<div class="group glass-card rounded-2xl p-6 sm:p-7 hover:-translate-y-1 transition-all duration-300">
    @if($icon)
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#2b5f83]/10 to-[#4a7c9b]/10 flex items-center justify-center mb-5 group-hover:from-[#2b5f83]/15 group-hover:to-[#4a7c9b]/15 transition-colors duration-300">
            <i class="{{ $icon }} text-[#2b5f83] text-lg"></i>
        </div>
    @endif

    @if($title)
        @if($href)
            <a href="{{ $href }}" class="block">
                <h3 class="text-base font-bold text-gray-900 group-hover:text-[#2b5f83] transition-colors duration-200 mb-2">{{ $title }}</h3>
            </a>
        @else
            <h3 class="text-base font-bold text-gray-900 mb-2">{{ $title }}</h3>
        @endif
    @endif

    <div class="text-gray-500 text-sm leading-relaxed">
        {{ $slot }}
    </div>
</div>