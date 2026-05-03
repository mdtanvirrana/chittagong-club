<div class="flex flex-col items-center justify-center px-8 py-3">
    <div class="relative mb-3">
        <div class="absolute -inset-4 rounded-full bg-primary/10 blur-3xl opacity-50"></div>
        <div class="relative flex h-32 w-32 items-center justify-center">
            <img
                class="h-32 w-32 rounded-full object-contain"
                src="{{ asset('logo.png') }}"
                alt="Chittagong Club Logo"
            />
        </div>
    </div>

    <h1 class="mb-2 text-center text-4xl font-extrabold tracking-tight">
        <span class="gold-text-gradient company-name-canterbury" style="font-family: 'Canterbury', serif;">{{ $companyName }}</span>
    </h1>

    <p class="text-center text-sm font-light uppercase tracking-widest text-gray-800">
        {{ $eyebrow ?? 'Exclusive Member Access' }}
    </p>

    @if (filled($sectionTitle ?? '') || filled($sectionDescription ?? '') || filled($stepLabel ?? ''))
        <div class="auth-floating-card mt-3 w-full max-w-md rounded-[1.75rem] px-5 py-5 text-center">
            @if (filled($stepLabel ?? ''))
                <p class="auth-step-pill">{{ $stepLabel }}</p>
            @endif

            @if (filled($sectionTitle ?? ''))
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $sectionTitle }}</h2>
            @endif

            @if (filled($sectionDescription ?? ''))
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $sectionDescription }}</p>
            @endif
        </div>
    @endif
</div>
