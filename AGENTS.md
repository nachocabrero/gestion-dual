# AGENTS.md — Gestión Académica IES Hermenegildo Lanz

## Protocolo

Este proyecto sigue **Spec-Driven Development (SDD)**. El SPEC.md es la fuente de verdad. Todo código debe cumplir el spec.

## Reglas

1. Leer SPEC.md antes de cualquier implementación
2. Leer progress/current.json para saber qué tarea está en progreso
3. Leer features.json para ver tareas pendientes
4. Marcar tarea como `in_progress` antes de implementar
5. Ejecutar `scripts/init.sh` antes y después de cada cambio
6. Marcar tarea como `done` tras validación
7. Actualizar progress/current.json y progress/history.md
8. Commit con mensajes descriptivos tras cada fase

## Arquitectura

```
┌─────────────────────────────────────────────────────┐
│                  IESH Lanz App                       │
├─────────────────────────────────────────────────────┤
│  Frontend: Blade + Tailwind + Alpine.js             │
│  Backend: Laravel 10 (PHP 8.1+)                     │
│  DB: MySQL (prod) / SQLite (dev)                    │
│  Auth: Laravel Breeze (email + password)             │
├─────────────────────────────────────────────────────┤
│  Roles: Alumno, Profesor, Coordinador Dual,          │
│         Empresa, Admin                               │
├─────────────────────────────────────────────────────┤
│  Módulos:                                            │
│  - Alumnado, Profesorado, Asignaturas, Calificaciones│
│  - Tutorías/Anotaciones, Horarios                    │
│  - Empresas, Prácticas, Proyectos                    │
│  - Portfolio Público, Notificaciones                 │
│  - Panel Admin, Historial de Cambios                 │
└─────────────────────────────────────────────────────┘
```

## Roles de Agente

- **Implementer:** Escribe código siguiendo el spec y las convenciones
- **Reviewer:** Verifica que el código cumple el spec y las reglas

## Stack

- Laravel 10
- PHP 8.1+
- MySQL / SQLite
- Tailwind CSS
- Alpine.js
- Blade Templates

## Comandos

```bash
# Iniciar proyecto
composer install
php artisan key:generate
php artisan migrate

# Servidor de desarrollo
php artisan serve

# Tests
php artisan test

# Limpieza
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```