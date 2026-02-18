@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title)
@section('meta_description', $page->seo_description ?? '')

@section('content')
    <x-hero :title="$page->title" :compact="true" />

    {{-- Regular content --}}
    @if($page->content)
        <article class="py-14 sm:py-20">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="prose prose-lg prose-gray max-w-none
                    prose-headings:font-extrabold prose-headings:text-gray-900 prose-headings:tracking-tight
                    prose-headings:mb-4 prose-headings:mt-8 first:prose-headings:mt-0
                    prose-p:mb-5 prose-p:leading-relaxed
                    prose-a:text-[#2b5f83] prose-a:no-underline hover:prose-a:underline
                    prose-img:rounded-2xl prose-img:shadow-lg
                    prose-ul:my-5 prose-ol:my-5 prose-li:my-1.5">
                    {!! $page->safe_content !!}
                </div>
            </div>
        </article>
    @endif

    {{-- Members block (Leden pagina) --}}
    @if($page->block('members.items'))
        @php $memberItems = $page->block('members.items'); @endphp
        <section class="py-14 sm:py-20 mesh-bg" x-data="{ openMember: null, ...logoSpotlight({{ count($memberItems) }}) }" x-init="start()">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if($page->block('members.intro'))
                    <div class="max-w-3xl mx-auto text-center mb-14">
                        <p class="text-gray-500 text-lg leading-relaxed">{{ $page->block('members.intro') }}</p>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($page->block('members.items') as $i => $member)
                        <div class="group glass-card rounded-2xl overflow-hidden hover:-translate-y-1.5 transition-all duration-300 animate-on-scroll animate-delay-{{ min(($i % 3 + 1) * 100, 300) }} flex flex-col cursor-pointer"
                             @click="openMember = {{ $i }}">
                            <div class="h-1 bg-gradient-to-r from-[#1a3f5c] via-[#2b5f83] to-[#4a7c9b] opacity-60 group-hover:opacity-100 transition-opacity"></div>
                            <div class="p-6 flex flex-col flex-1">
                                <div class="h-16 flex items-center justify-start mb-4">
                                    @if(!empty($member['logo']))
                                        <img src="{{ Storage::disk('public')->url($member['logo']) }}" alt="{{ $member['name'] }}"
                                             class="max-h-16 max-w-[180px] object-contain transition-all duration-700"
                                             :class="active === {{ $i }} ? 'grayscale-0 scale-105' : 'grayscale group-hover:grayscale-0'">
                                    @else
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#1a3f5c]/10 to-[#4a7c9b]/10 flex items-center justify-center">
                                            <i class="fa-solid fa-building text-2xl text-[#2b5f83]/30"></i>
                                        </div>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#2b5f83] transition-colors duration-200 mb-3">
                                    {{ $member['name'] }}
                                </h3>
                                @if(!empty($member['description']))
                                    <p class="text-gray-500 text-sm leading-relaxed line-clamp-3 flex-1">
                                        {{ Str::limit(strip_tags($member['description']), 120) }}
                                    </p>
                                @endif
                                <div class="mt-4 flex items-center gap-2 text-[#2b5f83] text-sm font-semibold opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <span>Meer info</span>
                                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Member detail modal --}}
            <template x-teleport="body">
                <div x-show="openMember !== null"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                     @keydown.escape.window="openMember = null"
                     x-cloak>
                    {{-- Backdrop --}}
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="openMember = null"></div>

                    {{-- Modal content --}}
                    @foreach($page->block('members.items') as $i => $member)
                        <div x-show="openMember === {{ $i }}"
                             x-transition:enter="transition ease-out duration-200 delay-75"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="relative bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-hidden flex flex-col">

                            {{-- Close button (floating) --}}
                            <button @click="openMember = null" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm hover:bg-white/40 flex items-center justify-center text-white/80 hover:text-white transition-all duration-200">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>

                            {{-- Compact gradient header with logo + name --}}
                            <div class="relative shrink-0 overflow-hidden" style="background: linear-gradient(135deg, #0f2027 0%, #1a3f5c 40%, #2b5f83 70%, #4a7c9b 100%);">
                                <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent 70%);"></div>
                                <div class="relative px-8 py-6 sm:px-10 sm:py-7 flex items-center gap-5">
                                    <div class="shrink-0 bg-white rounded-xl shadow-lg shadow-black/10 p-3 sm:p-4">
                                        @if(!empty($member['logo']))
                                            <img src="{{ Storage::disk('public')->url($member['logo']) }}" alt="{{ $member['name'] }}" class="max-h-8 sm:max-h-10 max-w-[140px] object-contain">
                                        @else
                                            <div class="w-10 h-10 flex items-center justify-center">
                                                <i class="fa-solid fa-building text-xl text-[#2b5f83]/30"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <h3 class="text-lg sm:text-xl font-bold text-white tracking-tight">{{ $member['name'] }}</h3>
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="px-8 py-6 sm:px-10 sm:py-8 overflow-y-auto flex-1">
                                @if(!empty($member['description']))
                                    <div class="prose prose-gray prose-sm sm:prose-base max-w-none
                                        prose-p:text-gray-600 prose-p:leading-relaxed prose-p:mb-4
                                        prose-headings:text-gray-900 prose-headings:mb-3 prose-headings:mt-6 first:prose-headings:mt-0
                                        prose-a:text-[#2b5f83]
                                        prose-ul:my-4 prose-ol:my-4 prose-li:my-1">
                                        {!! $member['description'] !!}
                                    </div>
                                @endif
                            </div>

                            {{-- Footer --}}
                            <div class="px-8 py-5 sm:px-10 border-t border-gray-100 flex items-center justify-between gap-4 shrink-0">
                                @if(!empty($member['url']))
                                    <a href="{{ $member['url'] }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-2.5 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-300 shadow-lg shadow-[#2b5f83]/25 hover:shadow-xl hover:shadow-[#2b5f83]/30 hover:-translate-y-0.5"
                                       style="background: linear-gradient(135deg, #1a3f5c, #2b5f83);" @click.stop>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs opacity-70"></i>
                                        Bezoek website
                                    </a>
                                @else
                                    <div></div>
                                @endif
                                <button @click="openMember = null" class="px-4 py-2 text-gray-400 hover:text-gray-600 text-sm font-medium transition-colors rounded-lg hover:bg-gray-50">
                                    Sluiten
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </template>
        </section>
    @endif

    {{-- Team block (Bestuur pagina) --}}
    @if($page->block('team.items'))
        <section class="py-14 sm:py-20 mesh-bg">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                @if($page->block('team.intro'))
                    <div class="max-w-3xl mx-auto text-center mb-14">
                        <p class="text-gray-500 text-lg leading-relaxed">{{ $page->block('team.intro') }}</p>
                    </div>
                @endif

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($page->block('team.items') as $i => $person)
                        <div class="group glass-card rounded-2xl overflow-hidden hover:-translate-y-1.5 transition-all duration-300 animate-on-scroll animate-delay-{{ min(($i % 3 + 1) * 100, 300) }}">
                            <div class="p-7 text-center">
                                @if(!empty($person['photo']))
                                    <div class="inline-block mb-5">
                                        <img src="{{ Storage::disk('public')->url($person['photo']) }}" alt="{{ $person['name'] }}" class="w-28 h-28 rounded-2xl object-cover shadow-lg shadow-black/10 group-hover:shadow-xl group-hover:shadow-[#2b5f83]/15 transition-shadow duration-300">
                                    </div>
                                @else
                                    <div class="inline-block mb-5">
                                        <div class="w-28 h-28 rounded-2xl bg-gradient-to-br from-[#1a3f5c] to-[#4a7c9b] flex items-center justify-center text-white text-2xl font-extrabold shadow-lg shadow-[#2b5f83]/20 group-hover:shadow-xl group-hover:shadow-[#2b5f83]/30 transition-shadow duration-300">
                                            {{ collect(explode(' ', $person['name']))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('') }}
                                        </div>
                                    </div>
                                @endif
                                <h3 class="text-lg font-bold text-gray-900">{{ $person['name'] }}</h3>
                                @if(!empty($person['role']))
                                    <p class="text-[#2b5f83] font-semibold text-sm mt-1.5">{{ $person['role'] }}</p>
                                @endif
                                @if(!empty($person['company']))
                                    <p class="text-gray-400 text-sm mt-0.5">{{ $person['company'] }}</p>
                                @endif
                                @if(!empty($person['bio']))
                                    <div class="mt-5 text-gray-500 text-sm leading-relaxed prose prose-sm max-w-none text-left">
                                        {!! $person['bio'] !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Child pages --}}
    @if($page->children->isNotEmpty())
        <section class="py-16 mesh-bg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($page->children()->published()->orderBy('sort_order')->get() as $child)
                        <x-card :title="$child->title" :href="url($child->full_slug)">
                            {{ Str::limit(strip_tags($child->content), 150) }}
                        </x-card>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection