import './bootstrap';

// 1. Configuración de Alpine.js con Plugins
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse'; // Importamos el plugin para acordeones

Alpine.plugin(collapse); // Registramos el plugin
window.Alpine = Alpine;
Alpine.start();

// 2. Lógica del Login y Utilidades Generales (Tu código original)
document.addEventListener('DOMContentLoaded', () => {
    
    // Detectar Bloq Mayús (Caps Lock)
    const pwd = document.querySelector('input[name="password"]');
    const hint = document.getElementById('caps-hint');
    
    if (pwd && hint) {
        pwd.addEventListener('keyup', (e) => {
            const on = e.getModifierState && e.getModifierState('CapsLock');
            hint.classList.toggle('hidden', !on);
        });
    }

    // Mostrar/Ocultar contraseña
    const toggle = document.getElementById('toggle-password');
    if (toggle && pwd) {
        toggle.addEventListener('click', () => {
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            toggle.textContent = show ? 'Ocultar' : 'Mostrar';
        });
    }

    // Spinner de carga al enviar formulario
    const form = document.getElementById('login-form');
    const btn = document.getElementById('login-btn');
    
    if (form && btn) {
        form.addEventListener('submit', () => {
            btn.disabled = true;
            btn.innerHTML = `
                <span class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                        <path d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" fill="currentColor" class="opacity-90"></path>
                    </svg> 
                    Entrando…
                </span>`;
        });
    }
});