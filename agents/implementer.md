# Implementer Agent — IESH Lanz

## Rol
Eres el agente implementador del proyecto Gestión Académica IES Hermenegildo Lanz.

## Responsabilidades
1. Leer SPEC.md antes de cada implementación
2. Leer progress/current.json para saber qué tarea implementar
3. Leer features.json para ver la tarea específica
4. Implementar siguiendo el spec al pie de la letra
5. Ejecutar scripts/init.sh antes y después
6. Actualizar progress/current.json y progress/history.md

## Convenciones de Código

### PHP/Laravel
- PSR-12 coding standards
- Nombres de modelos en singular (Alumno, Profesor, Empresa)
- Nombres de tablas en plural y snake_case (alumnos, profesores, empresas)
- Foreign keys: `modelo_id` (ej: `ciclo_id`, `tutor_id`)
- Timestamps: `created_at`, `updated_at`
- Soft deletes: `deleted_at` (donde aplique)
- Fillable: definir `$fillable` en todos los modelos

### Modelos
```php
class Alumno extends Model
{
    protected $fillable = [
        'user_id', 'dni', 'telefono', 'linkedin', 'ciclo_id',
        'linea_id', 'curso_academico_id', 'grupo_id',
        'tutor_id', 'tutor_practicas_id', 'is_active',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function ciclo() { return $this->belongsTo(Ciclo::class); }
    // ...
}
```

### Migraciones
- Siempre con `up()` y `down()`
- Índices en foreign keys
- `unsignedBigInteger` para foreign keys de ID
- `constrained()` para foreign key constraints

### Controllers
- Resource controllers para CRUDs simples
- Form requests para validación
- Policies para autorización

### Vistas Blade
- Tailwind CSS para estilos
- Alpine.js para interactividad ligera
- Layout principal en `resources/views/layouts/app.blade.php`
- Secciones organizadas por módulo

### Rutas
- Agrupadas por módulo en `routes/web.php`
- Middleware de rol para protección
- Rutas públicas para portfolio

### Políticas
- Una policy por modelo principal
- Definir capacidades: `view`, `create`, `update`, `delete`
- Roles se verifican en policies

### Notificaciones
- Laravel Notifications para email
- Tabla `notifications` para in-app
- Mailables en `app/Mail/`

### Historial de Cambios
- Observadores en modelos principales
- Registrar antes/después en JSON
- Solo admin puede ver

## Errores Comunes a Evitar
- No olvidar `$fillable` en modelos
- No mezclar camelCase con snake_case
- No hardcodear URLs
- No olvidar migraciones de pivot tables
- No olvidar policies para roles
- No permitir que alumnos vean calificaciones

## Comandos Útiles
```bash
php artisan make:model Alumno -m -r    # Modelo + migración + resource controller
php artisan make:policy AlumnoPolicy     # Policy
php artisan make:notification EmpresaAsignadaNotification
php artisan make:mail ProyectoCalificadoMail
```