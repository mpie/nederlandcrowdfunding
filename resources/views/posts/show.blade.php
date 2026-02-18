@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <x-hero :title="$post->title" :compact="true">
        <div class="mt-6 flex flex-wrap items-center gap-4" style="animation: slide-up 0.8s ease-out 0.3s both;">
            @if($post->published_at)
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm text-blue-100/70 text-sm border border-white/10">
                    <i class="fa-regular fa-calendar text-xs"></i>
                    {{ $post->published_at->format('d M Y') }}
                </span>
            @endif
            @if($post->author)
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm text-blue-100/70 text-sm border border-white/10">
                    <i class="fa-regular fa-user text-xs"></i>
                    {{ $post->author->name }}
                </span>
            @endif
            @php
                $wordCount = str_word_count(strip_tags($post->content ?? ''));
                $readingTime = max(1, (int) ceil($wordCount / 200));
            @endphp
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm text-blue-100/70 text-sm border border-white/10">
                <i class="fa-regular fa-clock text-xs"></i>
                {{ $readingTime }} min leestijd
            </span>
        </div>
    </x-hero>

    <article class="py-14 sm:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Article content --}}
            <div class="prose prose-lg prose-gray max-w-none
                prose-headings:font-extrabold prose-headings:text-gray-900 prose-headings:tracking-tight
                prose-headings:mb-4 prose-headings:mt-8 first:prose-headings:mt-0
                prose-p:mb-5 prose-p:leading-relaxed
                prose-a:text-[#2b5f83] prose-a:font-semibold prose-a:no-underline hover:prose-a:underline
                prose-strong:text-gray-900
                prose-img:rounded-2xl prose-img:shadow-lg
                prose-blockquote:border-[#2b5f83] prose-blockquote:bg-[#2b5f83]/[0.03] prose-blockquote:rounded-r-xl prose-blockquote:py-1 prose-blockquote:not-italic
                prose-ul:my-5 prose-ol:my-5 prose-li:my-1.5 prose-li:marker:text-[#2b5f83]">
                {!! $post->safe_content !!}
            </div>

            {{-- Share --}}
            <div class="mt-14 pt-8 border-t border-gray-100">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <a href="{{ route('posts.index') }}" class="group inline-flex items-center gap-2.5 text-[#2b5f83] font-semibold hover:gap-3 transition-all duration-300">
                        <i class="fa-solid fa-arrow-left text-sm group-hover:-translate-x-0.5 transition-transform"></i>
                        Terug naar actueel
                    </a>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 font-medium uppercase tracking-wider mr-1">Delen</span>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}"
                           target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-xl bg-gray-50 hover:bg-[#0077b5] text-gray-400 hover:text-white flex items-center justify-center transition-all duration-300 hover:shadow-lg hover:shadow-[#0077b5]/20">
                            <i class="fa-brands fa-linkedin-in text-sm"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}"
                           target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-xl bg-gray-50 hover:bg-black text-gray-400 hover:text-white flex items-center justify-center transition-all duration-300 hover:shadow-lg hover:shadow-black/20">
                            <i class="fa-brands fa-x-twitter text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Previous / Next --}}
            @if($previousPost || $nextPost)
                <nav class="mt-10 pt-8 border-t border-gray-100">
                    <div class="grid sm:grid-cols-2 gap-4">
                        @if($previousPost)
                            <a href="{{ route('posts.show', $previousPost) }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-gray-50/80 hover:bg-[#2b5f83]/[0.04] border border-transparent hover:border-[#2b5f83]/10 transition-all duration-300">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0 group-hover:shadow-md transition-shadow">
                                    <i class="fa-solid fa-arrow-left text-sm text-gray-400 group-hover:text-[#2b5f83] transition-colors"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Vorig bericht</span>
                                    <p class="mt-1 text-sm font-semibold text-gray-700 group-hover:text-[#2b5f83] transition-colors line-clamp-2">{{ $previousPost->title }}</p>
                                </div>
                            </a>
                        @else
                            <div></div>
                        @endif

                        @if($nextPost)
                            <a href="{{ route('posts.show', $nextPost) }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-gray-50/80 hover:bg-[#2b5f83]/[0.04] border border-transparent hover:border-[#2b5f83]/10 transition-all duration-300 sm:text-right sm:flex-row-reverse">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0 group-hover:shadow-md transition-shadow">
                                    <i class="fa-solid fa-arrow-right text-sm text-gray-400 group-hover:text-[#2b5f83] transition-colors"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Volgend bericht</span>
                                    <p class="mt-1 text-sm font-semibold text-gray-700 group-hover:text-[#2b5f83] transition-colors line-clamp-2">{{ $nextPost->title }}</p>
                                </div>
                            </a>
                        @endif
                    </div>
                </nav>
            @endif
        </div>
    </article>

    {{-- Related posts --}}
    @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
        <section class="relative py-20 sm:py-28 overflow-hidden">
            {{-- Background --}}
            <div class="absolute inset-0 hero-gradient opacity-[0.97]"></div>
            <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full opacity-[0.07]" style="background: radial-gradient(circle, white, transparent 70%);"></div>
            <div class="absolute bottom-0 right-1/4 w-72 h-72 rounded-full opacity-[0.05]" style="background: radial-gradient(circle, white, transparent 70%);"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest text-white/60 bg-white/8 border border-white/10 mb-4">Verder lezen</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Meer berichten</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($relatedPosts as $i => $related)
                        <a href="{{ route('posts.show', $related) }}"
                           class="group relative rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-2"
                           style="animation: slide-up 0.5s ease-out {{ ($i + 1) * 0.1 }}s both;">
                            <div class="absolute inset-0 bg-white/[0.07] backdrop-blur-sm border border-white/10 rounded-2xl group-hover:bg-white/[0.12] group-hover:border-white/20 transition-all duration-300"></div>
                            <div class="relative p-7 sm:p-8">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                        <i class="fa-regular fa-newspaper text-white/50 text-xs"></i>
                                    </div>
                                    <time class="text-xs font-semibold text-white/40 uppercase tracking-widest">
                                        {{ $related->published_at?->format('d M Y') }}
                                    </time>
                                </div>
                                <h3 class="text-lg font-bold text-white group-hover:text-white transition-colors line-clamp-2 leading-snug">
                                    {{ $related->title }}
                                </h3>
                                @if($related->excerpt)
                                    <p class="mt-3 text-white/40 text-sm line-clamp-2 leading-relaxed">{{ $related->excerpt }}</p>
                                @endif
                                <div class="mt-5 inline-flex items-center gap-2 text-white/60 text-sm font-semibold group-hover:text-white group-hover:gap-3 transition-all duration-300">
                                    Lees meer <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection