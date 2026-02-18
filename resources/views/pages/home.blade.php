@extends('layouts.app')

@section('title', 'Home')

@section('content')
    {{-- Hero --}}
    <x-hero
        :title="$page?->block('hero.title', 'Branchevereniging met impact op de Nederlandse economie')"
        :subtitle="$page?->block('hero.subtitle', 'Nederland Crowdfunding is de branchevereniging voor beleggings- en financieringsplatformen met een ECSP vergunning actief in Nederland.')"
    >
        <div class="mt-10 flex flex-wrap gap-4" style="animation: slide-up 0.8s ease-out 0.45s both;">
            @if($page?->block('hero.cta_text'))
                <a href="{{ $page->block('hero.cta_url', '/actueel') }}" class="group inline-flex items-center gap-2.5 px-7 py-3.5 bg-white text-[#2b5f83] font-semibold rounded-2xl hover:bg-white/95 transition-all duration-300 shadow-xl shadow-black/10 hover:shadow-2xl hover:shadow-black/15 hover:-translate-y-0.5">
                    <i class="fa-solid fa-newspaper text-sm opacity-60 group-hover:opacity-100 transition"></i>
                    {{ $page->block('hero.cta_text') }}
                </a>
            @endif
            @if($page?->block('hero.cta2_text'))
                <a href="{{ $page->block('hero.cta2_url', '/contact') }}" class="group inline-flex items-center gap-2.5 px-7 py-3.5 text-white font-semibold rounded-2xl hover:bg-white/10 transition-all duration-300 border border-white/20 backdrop-blur-sm hover:border-white/30">
                    <i class="fa-solid fa-envelope text-sm opacity-60 group-hover:opacity-100 transition"></i>
                    {{ $page->block('hero.cta2_text') }}
                </a>
            @endif
        </div>
    </x-hero>

    {{-- Content kaarten --}}
    @if($page?->block('cards'))
        <section class="relative py-24 sm:py-32 overflow-hidden bg-[#fafaf9]">
            {{-- Decorative mesh blobs --}}
            <div class="absolute top-10 left-[10%] w-[420px] h-[420px] rounded-full opacity-[0.15] blur-3xl" style="background: radial-gradient(circle, #06b6d4, transparent 70%);"></div>
            <div class="absolute bottom-10 right-[5%] w-[380px] h-[380px] rounded-full opacity-[0.12] blur-3xl" style="background: radial-gradient(circle, #a78bfa, transparent 70%);"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full opacity-[0.08] blur-3xl" style="background: radial-gradient(circle, #f59e0b, transparent 70%);"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Header --}}
                <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-14 gap-6">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest text-gray-500 bg-white border border-gray-200/80 shadow-sm mb-5">
                            Onze missie
                        </span>
                        <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 tracking-tight">
                            Wat doen wij?
                        </h2>
                        <p class="mt-5 text-gray-400 text-lg">Drie pijlers waar wij ons dagelijks voor inzetten</p>
                    </div>
                </div>

                @php
                    $cardStyles = [
                        [
                            'gradient' => 'linear-gradient(135deg, #0ea5e9, #06b6d4, #14b8a6)',
                            'light_bg' => '#0ea5e910',
                            'icon_bg'  => 'linear-gradient(135deg, #0ea5e9, #14b8a6)',
                            'tag_color'=> '#0ea5e9',
                            'num'      => '01',
                        ],
                        [
                            'gradient' => 'linear-gradient(135deg, #f59e0b, #f97316, #ef4444)',
                            'light_bg' => '#f59e0b10',
                            'icon_bg'  => 'linear-gradient(135deg, #f59e0b, #ef4444)',
                            'tag_color'=> '#f59e0b',
                            'num'      => '02',
                        ],
                        [
                            'gradient' => 'linear-gradient(135deg, #8b5cf6, #a78bfa, #c084fc)',
                            'light_bg' => '#8b5cf610',
                            'icon_bg'  => 'linear-gradient(135deg, #8b5cf6, #c084fc)',
                            'tag_color'=> '#8b5cf6',
                            'num'      => '03',
                        ],
                    ];
                @endphp

                {{-- Bento grid --}}
                <div class="grid md:grid-cols-3 gap-5 lg:gap-6">
                    @foreach($page->block('cards') as $i => $card)
                        @php $style = $cardStyles[$i % count($cardStyles)]; @endphp
                        <div class="group relative rounded-3xl transition-all duration-500 hover:-translate-y-1.5"
                             style="animation: slide-up 0.6s ease-out {{ ($i + 1) * 0.15 }}s both;">

                            {{-- Animated gradient border --}}
                            <div class="absolute -inset-px rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                                 style="background: {{ $style['gradient'] }}; padding: 1.5px;">
                                <div class="w-full h-full rounded-3xl bg-white"></div>
                            </div>

                            {{-- Card content --}}
                            <div class="relative h-full rounded-3xl bg-white border border-gray-200/60 shadow-sm group-hover:shadow-xl group-hover:shadow-gray-200/50 group-hover:border-transparent transition-all duration-500 overflow-hidden">
                                <div class="p-7 sm:p-8 h-full flex flex-col">
                                    {{-- Top row: number + icon --}}
                                    <div class="flex items-center justify-between mb-6">
                                        <span class="text-5xl sm:text-6xl font-black tracking-tight select-none leading-none"
                                              style="color: {{ $style['tag_color'] }}25;">
                                            {{ $style['num'] }}
                                        </span>
                                        @if(!empty($card['icon']))
                                            <div class="w-11 h-11 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 shrink-0 shadow-lg"
                                                 style="background: {{ $style['icon_bg'] }}; box-shadow: 0 4px 16px {{ $style['tag_color'] }}30;">
                                                <i class="{{ $card['icon'] }} text-base text-white"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <h3 class="text-xl font-bold text-gray-900 mb-3 leading-snug">{{ $card['title'] }}</h3>
                                    <div class="text-gray-500 text-sm leading-relaxed prose prose-sm max-w-none flex-1
                                        prose-p:text-gray-500 prose-strong:text-gray-700 prose-headings:text-gray-800
                                        prose-a:text-[#2b5f83] prose-a:no-underline hover:prose-a:underline
                                        prose-li:text-gray-500">
                                        {!! $card['content'] !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Over de branchevereniging + Statistieken --}}
    @if($page?->block('about.content') || $page?->block('stats.items'))
        <section class="py-20 sm:py-24 relative overflow-hidden bg-gradient-to-br from-[#f0f4f8] via-[#f5f7fa] to-[#eef2f7]">
            {{-- Subtle decorative blobs --}}
            <div class="absolute top-0 right-0 w-[600px] h-[600px] rounded-full opacity-[0.06]" style="background: radial-gradient(circle, #2b5f83 0%, transparent 70%);"></div>
            <div class="absolute bottom-0 left-[10%] w-[400px] h-[400px] rounded-full opacity-[0.04] blur-3xl" style="background: radial-gradient(circle, #0ea5e9, transparent 70%);"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-14 lg:gap-20 items-center">
                    @if($page?->block('about.content'))
                        <div>
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest text-gray-500 bg-white border border-gray-200/80 shadow-sm mb-5">Over ons</span>
                            <h2 class="section-heading text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight">
                                {{ $page->block('about.title', 'Over de branchevereniging') }}
                            </h2>
                            <div class="mt-6 text-gray-600 leading-relaxed prose prose-sm max-w-none">
                                {!! $page->block('about.content') !!}
                            </div>
                            @if($page->block('about.link_text'))
                                <a href="{{ $page->block('about.link_url', '#') }}" class="group inline-flex items-center gap-2 mt-8 text-[#2b5f83] font-semibold hover:gap-3 transition-all duration-300">
                                    {{ $page->block('about.link_text') }}
                                    <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-0.5 transition-transform"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                    @if($page?->block('stats.items'))
                        <div class="relative">
                            {{-- Glow behind card --}}
                            <div class="absolute -inset-4 rounded-3xl opacity-20 blur-2xl" style="background: linear-gradient(135deg, #1a3f5c, #4a7c9b);"></div>
                            <div class="relative rounded-3xl overflow-hidden hero-gradient shadow-2xl shadow-[#1a3f5c]/30">
                                <div class="p-8 sm:p-10 text-white relative">
                                    {{-- Inner glass circles --}}
                                    <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent 70%); animation: float 8s ease-in-out infinite;"></div>

                                    <h3 class="text-xl font-bold">{{ $page->block('stats.title', 'Nederland Crowdfunding') }}</h3>
                                    <p class="text-blue-200/50 text-lg mt-1">{{ $page->block('stats.subtitle', 'versterkt het klimaat voor MKB-financiering') }}</p>
                                    <div class="mt-8 grid grid-cols-2 gap-3">
                                        @foreach($page->block('stats.items') as $stat)
                                            <div class="glass-dark rounded-2xl p-5 text-center hover:bg-white/12 transition-colors duration-300">
                                                @if(mb_strtolower($stat['label']) === 'leden')
                                                    <div class="text-3xl font-extrabold tracking-tight">{{ $memberCount }}</div>
                                                @else
                                                    <div class="text-3xl font-extrabold tracking-tight">{!! $stat['value'] !!}</div>
                                                @endif
                                                <div class="text-blue-200/40 text-sm mt-1.5 font-medium">{{ $stat['label'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if($page?->block('faq.items'))
        <section class="relative py-24 sm:py-32 overflow-hidden bg-[#fafaf9]">
            {{-- Decorative blobs --}}
            <div class="absolute top-20 right-[10%] w-[350px] h-[350px] rounded-full opacity-[0.10] blur-3xl" style="background: radial-gradient(circle, #0ea5e9, transparent 70%);"></div>
            <div class="absolute bottom-20 left-[5%] w-[300px] h-[300px] rounded-full opacity-[0.08] blur-3xl" style="background: radial-gradient(circle, #8b5cf6, transparent 70%);"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-5 gap-12 lg:gap-16" x-data="{ active: null }">
                    {{-- Left: heading --}}
                    <div class="lg:col-span-2 lg:sticky lg:top-28 lg:self-start">
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest text-gray-500 bg-white border border-gray-200/80 shadow-sm mb-5">
                            FAQ
                        </span>
                        <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 tracking-tight">
                            {{ $page->block('faq.title', 'Veelgestelde vragen') }}
                        </h2>
                        @if($page->block('faq.subtitle'))
                            <p class="mt-4 text-gray-400 text-lg leading-relaxed">{{ $page->block('faq.subtitle') }}</p>
                        @endif
                    </div>

                    {{-- Right: accordion --}}
                    <div class="lg:col-span-3 space-y-3">
                        @foreach($page->block('faq.items') as $i => $faq)
                            <div class="group rounded-2xl overflow-hidden transition-all duration-300 bg-white border shadow-sm"
                                 :class="active === {{ $i }}
                                    ? 'border-transparent shadow-lg shadow-gray-200/50 ring-1 ring-gray-200/50'
                                    : 'border-gray-200/60 hover:border-gray-300/60 hover:shadow-md hover:shadow-gray-100'">
                                <button @click="active = active === {{ $i }} ? null : {{ $i }}"
                                        class="w-full px-6 py-5 text-left flex items-center gap-4 cursor-pointer transition-colors duration-200">
                                    {{-- Number --}}
                                    <span class="text-xs font-bold tabular-nums shrink-0 w-7 h-7 rounded-lg flex items-center justify-center transition-all duration-300"
                                          :class="active === {{ $i }}
                                            ? 'bg-gradient-to-br from-[#0ea5e9] to-[#8b5cf6] text-white shadow-md'
                                            : 'bg-gray-100 text-gray-400'">
                                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="font-semibold text-gray-900 text-[15px] flex-1">{{ $faq['question'] }}</span>
                                    {{-- Plus/minus icon --}}
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-all duration-300"
                                         :class="active === {{ $i }} ? 'bg-gray-900 rotate-45' : 'bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 transition-colors duration-300" :class="active === {{ $i }} ? 'text-white' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                                        </svg>
                                    </div>
                                </button>
                                <div x-show="active === {{ $i }}" x-collapse x-cloak>
                                    <div class="px-6 pb-6 pl-[4.25rem] text-gray-500 text-sm leading-relaxed prose prose-sm max-w-none
                                        prose-p:text-gray-500 prose-strong:text-gray-700">
                                        {!! $faq['answer'] !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Leden logos --}}
    @if($memberLogos->isNotEmpty())
        <section class="py-20 sm:py-24 relative overflow-hidden"
                 x-data="logoSpotlight({{ $memberLogos->count() }})"
                 x-init="start()">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest text-gray-500 bg-white border border-gray-200/80 shadow-sm mb-5">Onze leden</span>
                    <h2 class="section-heading text-3xl sm:text-4xl font-extrabold text-gray-900">Aangesloten platforms</h2>
                    <p class="mt-5 text-gray-400">{{ $memberLogos->count() }} professionele crowdfundingplatforms</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($memberLogos as $idx => $member)
                        <a href="{{ $member['url'] ?? '#' }}" target="_blank" rel="noopener"
                           class="group glass-card flex items-center justify-center h-24 px-5 rounded-2xl hover:-translate-y-1 transition-all duration-300"
                           title="{{ $member['name'] }}">
                            <img src="{{ Storage::disk('public')->url($member['logo']) }}"
                                 alt="{{ $member['name'] }}"
                                 class="max-h-12 max-w-full object-contain transition-all duration-700"
                                 :class="active === {{ $idx }} ? 'grayscale-0 opacity-100 scale-110' : 'grayscale opacity-40 group-hover:grayscale-0 group-hover:opacity-100'">
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-10">
                    <a href="/over-ons/leden" class="group inline-flex items-center gap-2 text-[#2b5f83] font-semibold hover:gap-3 transition-all duration-300 text-sm">
                        Bekijk alle leden <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- Blog preview --}}
    @if($latestPosts->isNotEmpty())
        <section class="relative py-20 sm:py-28 overflow-hidden">
            {{-- Background --}}
            <div class="absolute inset-0 hero-gradient opacity-[0.97]"></div>
            <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full opacity-[0.07]" style="background: radial-gradient(circle, white, transparent 70%);"></div>
            <div class="absolute bottom-0 right-1/4 w-72 h-72 rounded-full opacity-[0.05]" style="background: radial-gradient(circle, white, transparent 70%);"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 gap-6">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest text-white/60 bg-white/8 border border-white/10 mb-5">Actueel</span>
                        <h2 class="section-heading section-heading--light text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Laatste nieuws</h2>
                        <p class="mt-5 text-white/40 text-lg">Blijf op de hoogte van de laatste ontwikkelingen</p>
                    </div>
                    <a href="{{ route('posts.index') }}" class="hidden sm:inline-flex items-center gap-2.5 px-6 py-3 rounded-xl text-sm font-semibold text-white border border-white/15 hover:bg-white/10 hover:border-white/25 transition-all duration-300 group shrink-0">
                        Alle berichten
                        <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($latestPosts as $i => $post)
                        <a href="{{ route('posts.show', $post) }}"
                           class="group relative rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-2"
                           style="animation: slide-up 0.5s ease-out {{ ($i + 1) * 0.1 }}s both;">
                            <div class="absolute inset-0 bg-white/[0.07] backdrop-blur-sm border border-white/10 rounded-2xl group-hover:bg-white/[0.12] group-hover:border-white/20 transition-all duration-300"></div>
                            <div class="relative p-7 sm:p-8">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                        <i class="fa-regular fa-newspaper text-white/50 text-xs"></i>
                                    </div>
                                    <time class="text-xs font-semibold text-white/40 uppercase tracking-widest">
                                        {{ $post->published_at?->format('d M Y') }}
                                    </time>
                                </div>
                                <h3 class="text-lg font-bold text-white line-clamp-2 leading-snug">
                                    {{ $post->title }}
                                </h3>
                                @if($post->excerpt)
                                    <p class="mt-3 text-white/40 text-sm line-clamp-3 leading-relaxed">{{ $post->excerpt }}</p>
                                @endif
                                <div class="mt-5 inline-flex items-center gap-2 text-white/60 text-sm font-semibold group-hover:text-white group-hover:gap-3 transition-all duration-300">
                                    Lees meer <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-10 sm:hidden">
                    <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl text-sm font-semibold text-white border border-white/15 hover:bg-white/10 transition-all duration-300">
                        Alle berichten <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection