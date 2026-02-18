@extends('layouts.app')

@section('title', 'Actueel')

@section('content')
    <x-hero title="Actueel" subtitle="Het laatste nieuws van de branchevereniging" :compact="true" />

    <section class="py-14 sm:py-20 mesh-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($posts->isEmpty())
                <div class="text-center py-24">
                    <div class="w-20 h-20 rounded-2xl glass-card flex items-center justify-center mx-auto mb-5">
                        <i class="fa-solid fa-newspaper text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-500 text-lg">Er zijn nog geen berichten.</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    @foreach($posts as $i => $post)
                        <article class="group glass-card rounded-2xl hover:-translate-y-1.5 transition-all duration-300 overflow-hidden animate-on-scroll animate-delay-{{ min(($i % 3 + 1) * 100, 300) }}">
                            <div class="p-7 sm:p-8">
                                <time class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2b5f83]/50 uppercase tracking-widest">
                                    <span class="w-1 h-1 rounded-full bg-[#2b5f83]/30"></span>
                                    {{ $post->published_at?->format('d M Y') }}
                                </time>
                                <a href="{{ route('posts.show', $post) }}" class="block mt-4">
                                    <h2 class="text-lg font-bold text-gray-900 group-hover:text-[#2b5f83] transition-colors duration-200 line-clamp-2">
                                        {{ $post->title }}
                                    </h2>
                                </a>
                                @if($post->excerpt)
                                    <p class="mt-3 text-gray-500 text-sm line-clamp-3 leading-relaxed">{{ $post->excerpt }}</p>
                                @endif
                                <div class="mt-6 flex items-center justify-between">
                                    <a href="{{ route('posts.show', $post) }}" class="inline-flex items-center gap-2 text-[#2b5f83] text-sm font-semibold group/link hover:gap-3 transition-all duration-300">
                                        Lees meer <i class="fa-solid fa-arrow-right text-xs group-hover/link:translate-x-0.5 transition-transform"></i>
                                    </a>
                                    @if($post->author)
                                        <span class="text-xs text-gray-400 font-medium">{{ $post->author->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-14">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection