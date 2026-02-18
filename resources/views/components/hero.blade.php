@props(['title' => '', 'subtitle' => '', 'compact' => false])

<section class="relative overflow-hidden {{ $compact ? 'py-14 sm:py-18' : 'py-24 sm:py-32 lg:py-40' }} hero-gradient">
    {{-- Animated floating orbs --}}
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-20 -right-20 w-[500px] h-[500px] rounded-full opacity-30" style="background: radial-gradient(circle, rgba(74,124,155,0.4) 0%, transparent 70%); animation: float-slow 8s ease-in-out infinite;"></div>
        <div class="absolute top-1/3 -left-32 w-[350px] h-[350px] rounded-full opacity-20" style="background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); animation: float 10s ease-in-out infinite;"></div>
        <div class="absolute -bottom-24 right-1/4 w-[400px] h-[400px] rounded-full opacity-20" style="background: radial-gradient(circle, rgba(74,124,155,0.3) 0%, transparent 70%); animation: float-slow 12s ease-in-out infinite reverse;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full" style="background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 50%); animation: pulse-glow 6s ease-in-out infinite;"></div>
    </div>

    {{-- Noise texture --}}
    <div class="absolute inset-0 opacity-[0.015]" style="background-image: url('data:image/svg+xml,<svg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22><filter id=%22n%22><feTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/></filter><rect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23n)%22/></svg>');"></div>

    {{-- Glass mesh lines --}}
    <div class="absolute inset-0 opacity-[0.04]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22><rect width=%2280%22 height=%2280%22 fill=%22none%22 stroke=%22white%22 stroke-width=%220.3%22/></svg>');"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl" style="animation: slide-up 0.8s ease-out both;">
            <h1 class="text-white {{ $compact ? 'text-2xl sm:text-3xl lg:text-4xl' : 'text-3xl sm:text-5xl lg:text-6xl' }} font-extrabold tracking-tight leading-[1.08]">
                {{ $title }}
            </h1>
            @if($subtitle)
                <p class="mt-6 text-lg sm:text-xl text-blue-100/60 max-w-2xl leading-relaxed" style="animation: slide-up 0.8s ease-out 0.15s both;">
                    {{ $subtitle }}
                </p>
            @endif
            <div style="animation: slide-up 0.8s ease-out 0.3s both;">
                {{ $slot }}
            </div>
        </div>
    </div>

    {{-- Bottom glass edge --}}
    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
</section>