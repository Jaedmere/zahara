<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Zahara · Ingresar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#0B2A5B">

  @vite(['resources/css/app.css','resources/js/app.js'])

  <link rel="icon" type="image/svg+xml" href="{{ asset('img/icono.svg') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/apple-touch-icon.png') }}">
</head>
<body class="min-h-[100svh] text-white">
  {{-- FONDO --}}
  <div class="fixed inset-0 -z-10 overflow-hidden">
    {{-- Poster/imagen para móvil --}}
    <img
      src="{{ asset('img/zahara-login-poster.jpg') }}"
      alt=""
      class="w-full h-full object-cover md:hidden"
      decoding="async" loading="eager"
    >
    {{-- Video en desktop/tablet --}}
    <video class="w-full h-full object-cover hidden md:block"
           autoplay muted loop playsinline preload="none"
           poster="{{ asset('img/zahara-login-poster.jpg') }}">
      <source src="{{ asset('video/login-bg.webm') }}" type="video/webm">
      <source src="{{ asset('video/login-bg.mp4') }}" type="video/mp4">
    </video>
    {{-- Scrim + noise encima --}}
    <div class="absolute inset-0 bg-gradient-to-br from-[#081326]/85 via-[#0B2A5B]/70 to-[#0B2A5B]/55"></div>
    <div class="absolute inset-0 bg-noise"></div>
  </div>

  <main class="min-h-[100svh] flex items-center justify-center p-4"
        style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);">
    <section class="w-full max-w-xl">
      <div class="glass-panel rounded-2xl px-6 py-8 sm:px-10 sm:py-9 small-halo">
        {{-- Marca --}}
        <div class="flex flex-col items-center gap-3 mb-7 text-center">
          <div class="glass-logo flex items-center gap-2 rounded-2xl px-3 py-2 border border-white/30 bg-white/10 backdrop-blur">
            <img src="{{ asset('img/logo.svg') }}" alt="Zahara"
                 class="w-12 h-12 sm:w-16 sm:h-16 object-contain drop-shadow-[0_0_10px_rgba(19,163,216,0.6)]" />
          </div>
          <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight drop-shadow">Zahara V2.0</h1>
          <p class="text-sm text-slate-200/90">Gestión de CxC</p>
        </div>

        {{-- Watermark (oculta en pantallas pequeñas) --}}
        <svg class="card-watermark mx-auto hidden sm:block" viewBox="0 0 520 140" fill="none" aria-hidden="true">
          <path d="M10 120 C 120 60, 240 90, 340 50 S 510 40, 510 40"
                stroke="url(#g1)" stroke-width="6" stroke-linecap="round" fill="none"/>
          <g opacity=".9">
            <rect x="120" y="70" width="14" height="40" rx="4" fill="#13A3D8"/>
            <rect x="160" y="55" width="14" height="55" rx="4" fill="#13A3D8"/>
            <rect x="200" y="45" width="14" height="65" rx="4" fill="#13A3D8"/>
            <rect x="240" y="30" width="14" height="80" rx="4" fill="#13A3D8"/>
          </g>
          <defs>
            <linearGradient id="g1" x1="0" x2="520" y1="0" y2="0">
              <stop stop-color="#13A3D8"/><stop offset="1" stop-color="#1876D1"/>
            </linearGradient>
          </defs>
        </svg>

        @if ($errors->any())
          <div class="mb-4 text-sm text-red-200 bg-red-900/30 border border-red-500/40 rounded-lg p-3">
            {{ $errors->first() }}
          </div>
        @endif

        <form id="login-form" method="POST" action="{{ route('login.post') }}" class="space-y-4">
          @csrf

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm mb-1 text-slate-200">Correo</label>
            <input id="email" name="email" type="email" required
                   autocomplete="username" inputmode="email"
                   autocapitalize="off" spellcheck="false"
                   value="{{ old('email') }}"
                   class="input-pill bg-white/95 text-slate-900 text-base">
          </div>

          {{-- Password --}}
          <div>
            <label for="password" class="block text-sm mb-1 text-slate-200">Contraseña</label>
            <div class="relative">
              <input id="password" name="password" type="password" required
                     autocomplete="current-password" inputmode="text"
                     class="input-pill bg-white/95 text-slate-900 pr-24 text-base">
              <button type="button" id="toggle-password"
                      class="absolute inset-y-0 right-1 my-1 px-3 text-sm rounded-xl bg-slate-100 text-slate-800 hover:bg-slate-200"
                      aria-pressed="false" aria-controls="password">Mostrar</button>
            </div>
            <p id="caps-hint" class="hidden mt-1 text-xs text-amber-300">Bloq Mayús activado</p>
          </div>

          <div class="flex items-center justify-between text-sm text-slate-200">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" name="remember" class="rounded border-white/30 bg-white/90">
              Recuérdame
            </label>
            <a href="#" class="hover:underline">¿Olvidaste tu clave?</a>
          </div>

          <button id="login-btn" type="submit"
                  class="btn-amber w-full py-2 rounded-2xl">
            Entrar
          </button>
        </form>

        <div class="mt-7 text-center text-xs text-slate-200/80">
          © {{ date('Y') }} Zahara · Gestión de CxC
        </div>
      </div>
    </section>
  </main>
</body>
</html>
