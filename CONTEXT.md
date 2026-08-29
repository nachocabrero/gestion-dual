# Contexto del Proyecto — IESH Lanz

**Fecha:** 2026-08-29  
**Estado:** Feature `emp-001` completada. Siguiente: `prac-001` (Ofertas y Solicitud de Prácticas)  
**Tests:** 153/153 passing (antes de este fix)

---

## Problema Detectado

Los tests de `OfertaPracticaTest` fallan (20 failed, 2 passed) porque los modelos no tienen `$table` definido:

- `OfertaPractica` → Laravel infiere `oferta_practicas` (singular), pero la tabla es `ofertas_practicas` (plural)
- `SolicitudPractica` → Laravel infiere `solicitud_practica` (singular), pero la tabla es `solicitudes_practicas` (plural)
- `Practica` → Laravel infiere `practica` (singular), pero la tabla es `practicas` (plural)

Las migraciones crean las tablas con nombre plural correctamente, pero los modelos no lo especifican.

## Archivos a Modificar

1. `app/Models/OfertaPractica.php` — añadir `protected $table = 'ofertas_practicas';`
2. `app/Models/SolicitudPractica.php` — añadir `protected $table = 'solicitudes_practicas';`
3. `app/Models/Practica.php` — añadir `protected $table = 'practicas';`

## Estado de Features

| ID | Título | Estado |
|----|--------|--------|
| auth-001 | Autenticación y roles | ✅ completed |
| acad-001 | Estructura académica | ✅ completed |
| alum-001 | Módulo Alumnado | ✅ completed |
| prof-001 | Módulo Profesorado | ✅ completed |
| asig-001 | Asignaturas, Grupos y Calificaciones | ✅ completed |
| tutor-001 | Tutorías y Anotaciones | ✅ completed |
| emp-001 | Módulo Empresas | ✅ completed |
| prac-001 | Ofertas y Solicitud de Prácticas | ⏸ pending (bloqueado por bug de tabla) |
| prac-002 | Gestión de Prácticas y Horas | pending |
| proj-001 | Módulo de Proyectos (2º) | pending |
| proj-002 | Portfolio Público | pending |
| notif-001 | Notificaciones | pending |
| admin-001 | Panel Admin | pending |
| hist-001 | Historial de Cambios | pending |
| db-001 | (sin especificar) | pending |

## Estado de Tests (antes del fix)

- `OfertaPracticaTest.php`: 20 failed, 2 passed
- Tests globales: 153 passing (antes de ejecutar OfertaPracticaTest)

## Archivos Existentes para Prácticas

**Migraciones:**
- `2026_08_29_160000_create_practicas_tables.php` — crea ofertas_practicas, solicitudes_practicas, practicas, proyectos, proyecto_imagenes

**Modelos:**
- `app/Models/OfertaPractica.php` — morphTo creador, hasMany solicitudes, belongsToMany alumnos_destinatarios
- `app/Models/SolicitudPractica.php` — belongsTo oferta, belongsTo alumno
- `app/Models/Practica.php` — belongsTo alumno, oferta, empresa, tutor_laboral

**Controllers:**
- `app/Http/Controllers/OfertaPracticaController.php` — index, create, store, show, edit, update, destroy, postularse, retirar, aceptar, rechazar, solicitudes, misOfertas

**Vistas:**
- ❌ NO existen vistas de ofertas/prácticas (solo hay directorios: admin, alumnos, anotaciones, auth, calificaciones, componentes, emails, empresas, layouts, notificaciones, privacy, profesores, profile, rgpd, welcome)

**Factories:**
- `database/factories/OfertaPracticaFactory.php`
- `database/factories/SolicitudPracticaFactory.php`
- `database/factories/PracticaFactory.php`

**Tests:**
- `tests/Feature/OfertaPracticaTest.php` — 22 tests (20 fallan por bug de tabla)

## Pasos Siguientes

1. Arreglar `$table` en los 3 modelos
2. Ejecutar `php artisan test --filter=OfertaPractica` para verificar que pasan todos
3. Ejecutar tests globales para confirmar que no se rompió nada
4. Crear vistas para ofertas/prácticas (index, create, show, edit, solicitudes, mis-ofertas)
5. Completar `prac-001`
6. Implementar `prac-002` (Gestión de Prácticas y Horas)