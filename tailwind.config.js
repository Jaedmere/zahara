// tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php", // MUY IMPORTANTE: Donde Tailwind buscará tus clases
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'brand-dark': 'var(--color-brand-dark)',
        'brand-mid': 'var(--color-brand-mid)',
        'brand-cyan': 'var(--color-brand-cyan)',
        'brand-gold': 'var(--color-brand-gold)',
        // Colores de tema que puedes usar en el HTML como `bg-app-bg`
        'app-bg': '#0b1220',
        'header-bg': '#0c1426',
        'text-base': '#e5e7eb',
        'text-muted': '#94a3b8',
        'border-light': 'rgba(255,255,255,.10)',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'Segoe UI', 'Roboto', 'Ubuntu', 'Arial', 'sans-serif'],
      },
      boxShadow: {
        // Nombres de sombras para usar en CSS con `box-shadow: theme('boxShadow.panel');`
        'panel': '0 30px 80px rgba(0,0,0,.45)',
        'btn-amber': '0 8px 20px rgba(242,178,51,.35), inset 0 1px 0 rgba(255,255,255,.25)',
        'btn-amber-hover': '0 10px 26px rgba(242,178,51,.45), inset 0 1px 0 rgba(255,255,255,.28)',
      },
    },
  },
  plugins: [],
}