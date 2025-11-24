# Módulo CxC EDS para Laravel

Este paquete incluye migraciones, modelos, controladores, vistas y seeders para un sistema de Cuentas por Cobrar con EDS como sucursales.

## Requisitos
- PHP 8.2+
- MySQL/MariaDB
- Node/NPM (para assets)
- Laravel 11 (recomendado)

## Pasos de instalación
1. Crear proyecto Laravel nuevo (o usar uno existente):
   ```bash
   composer create-project laravel/laravel cxc-eds
   cd cxc-eds
   ```

2. Autenticación base (Breeze Blade recomendado):
   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install blade
   npm install && npm run build
   ```

3. Copia el contenido de este módulo dentro de tu proyecto **sobrescribiendo** rutas y agregando archivos (no incluye vendor).

4. Migraciones y seeders:
   ```bash
   php artisan migrate
   php artisan db:seed --class=Database\\Seeders\\RoleSeeder
   php artisan db:seed --class=Database\\Seeders\\DemoSeeder
   ```

5. Inicia el servidor:
   ```bash
   php artisan serve
   ```

## Usuarios demo
- **admin@example.com** / **password**

## Notas
- Las políticas de autorización simples están en `AuthServiceProvider` vía Gates.
- Agrega colas y mailers para recordatorios de vencimientos según tu configuración.
- Las vistas son básicas para empezar; personalízalas con tu estilo (Tailwind).
