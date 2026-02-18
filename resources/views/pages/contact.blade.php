@extends('layouts.app')

@section('title', 'Contact')

@section('content')
    <x-hero title="Contact" subtitle="Heeft u een vraag? Neem gerust contact met ons op." :compact="true" />

    <section class="py-14 sm:py-20 mesh-bg">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-5 gap-10 lg:gap-14">
                {{-- Contact info --}}
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <h2 class="text-xl font-extrabold text-gray-900 mb-4">Branchevereniging Nederland Crowdfunding</h2>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Heeft u een vraag over crowdfunding, ons platform of de branchevereniging? Vul het formulier in en wij nemen zo snel mogelijk contact met u op.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <a href="mailto:info@nederlandcrowdfunding.nl" class="group glass-card rounded-2xl p-5 flex items-center gap-4 hover:-translate-y-0.5 transition-all duration-300">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#2b5f83]/10 to-[#4a7c9b]/10 flex items-center justify-center shrink-0 group-hover:from-[#2b5f83]/15 group-hover:to-[#4a7c9b]/15 transition-colors">
                                <i class="fa-solid fa-envelope text-[#2b5f83]"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">E-mail</p>
                                <p class="text-sm font-semibold text-gray-900 group-hover:text-[#2b5f83] transition-colors">info@nederlandcrowdfunding.nl</p>
                            </div>
                        </a>

                        <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#2b5f83]/10 to-[#4a7c9b]/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-shield-halved text-[#2b5f83]"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Privacy</p>
                                <p class="text-sm text-gray-600">Uw gegevens worden alleen gebruikt om contact met u op te nemen.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact form --}}
                <div class="lg:col-span-3">
                    @if(session('success'))
                        <div class="glass-card rounded-2xl p-8 text-center" style="animation: slide-up 0.5s ease-out both;">
                            <div class="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center mx-auto mb-5">
                                <i class="fa-solid fa-check text-green-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Bericht verzonden</h3>
                            <p class="text-gray-500">{{ session('success') }}</p>
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mt-6 text-[#2b5f83] font-semibold text-sm hover:gap-3 transition-all duration-300">
                                Terug naar home <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('contact.submit') }}" class="glass-card rounded-2xl p-7 sm:p-8 space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf

                            {{-- Honeypot: invisible to users, bots fill it in --}}
                            <div class="absolute -left-[9999px]" aria-hidden="true">
                                <label for="website_url">Website</label>
                                <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off" value="">
                            </div>

                            {{-- Timestamp token --}}
                            <input type="hidden" name="_form_token" value="{{ time() }}">

                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Naam <span class="text-red-400">*</span></label>
                                    <input type="text" name="name" id="name" required minlength="2" maxlength="255"
                                           value="{{ old('name') }}"
                                           placeholder="Uw volledige naam"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur-sm text-gray-900 text-sm placeholder-gray-400 focus:ring-2 focus:ring-[#2b5f83]/20 focus:border-[#2b5f83] outline-none transition-all duration-200">
                                    @error('name')
                                        <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">E-mail <span class="text-red-400">*</span></label>
                                    <input type="email" name="email" id="email" required maxlength="255"
                                           value="{{ old('email') }}"
                                           placeholder="uw@email.nl"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur-sm text-gray-900 text-sm placeholder-gray-400 focus:ring-2 focus:ring-[#2b5f83]/20 focus:border-[#2b5f83] outline-none transition-all duration-200">
                                    @error('email')
                                        <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1.5">Telefoon</label>
                                    <input type="tel" name="phone" id="phone" maxlength="20"
                                           value="{{ old('phone') }}"
                                           placeholder="+31 6 12345678"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur-sm text-gray-900 text-sm placeholder-gray-400 focus:ring-2 focus:ring-[#2b5f83]/20 focus:border-[#2b5f83] outline-none transition-all duration-200">
                                </div>

                                <div>
                                    <label for="subject" class="block text-sm font-semibold text-gray-700 mb-1.5">Onderwerp</label>
                                    <input type="text" name="subject" id="subject" maxlength="255"
                                           value="{{ old('subject') }}"
                                           placeholder="Waar gaat uw vraag over?"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur-sm text-gray-900 text-sm placeholder-gray-400 focus:ring-2 focus:ring-[#2b5f83]/20 focus:border-[#2b5f83] outline-none transition-all duration-200">
                                </div>
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-semibold text-gray-700 mb-1.5">Bericht <span class="text-red-400">*</span></label>
                                <textarea name="message" id="message" rows="5" required minlength="10" maxlength="5000"
                                          placeholder="Schrijf hier uw bericht..."
                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur-sm text-gray-900 text-sm placeholder-gray-400 focus:ring-2 focus:ring-[#2b5f83]/20 focus:border-[#2b5f83] outline-none transition-all duration-200 resize-y min-h-[120px]">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between gap-4 pt-2">
                                <p class="text-xs text-gray-400">
                                    <i class="fa-solid fa-lock text-[10px] mr-1"></i>
                                    Uw gegevens worden veilig verwerkt
                                </p>
                                <button type="submit"
                                        :disabled="submitting"
                                        :class="submitting ? 'opacity-60 cursor-wait' : 'hover:from-[#234d6b] hover:to-[#356f97] hover:-translate-y-px hover:shadow-[#2b5f83]/40'"
                                        class="inline-flex items-center gap-2.5 px-7 py-3 bg-gradient-to-r from-[#1a3f5c] to-[#2b5f83] text-white text-sm font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-[#2b5f83]/25">
                                    <template x-if="!submitting">
                                        <span class="inline-flex items-center gap-2">
                                            <i class="fa-solid fa-paper-plane text-xs"></i>
                                            Versturen
                                        </span>
                                    </template>
                                    <template x-if="submitting">
                                        <span class="inline-flex items-center gap-2">
                                            <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                            Verzenden...
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection