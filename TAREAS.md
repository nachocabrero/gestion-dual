# Tareas Pendientes — IES Hermenegildo Lanz

## Estado Actual
- **Tests:** 25 fallidos, 227 passing (252 total)
- **Último commit:** `4199f04`
- **Base de datos:** MySQL (127.0.0.1:3307, db: ieshlanz)

---

## T1: Crear vistas de oferta de empleo (create + edit)
**Prioridad:** Alta | **Estado:** Pendiente

- [ ] Crear `resources/views/ofertas/create.blade.php`
- [ ] Crear `resources/views/ofertas/edit.blade.php`
- [ ] Verificar que `OfertaPracticaController` pasa las variables correctas
- [ ] Verificar que las rutas `ofertas.create` y `ofertas.edit` existen

**Problema:** Error 500 al pulsar "crear oferta de empleo" — la vista `ofertas/create.blade.php` no existe.

---

## T2: Arreglar error 500 en empresa edit (guardar/cancelar)
**Prioridad:** Alta | **Estado:** Pendiente

- [ ] Investigar el error exacto (ver logs)
- [ ] El seeder `IesDataSeeder` intenta insertar `ciclo_id` y `curso_academico` en `convenios` — esas columnas ya no existen
- [ ] Actualizar el seeder para usar las nuevas columnas de convenio: `alumno_id`, `grupo_id`, `tutor_laboral_id`, `tutor_docente_id`, `numero_horas`, `fecha_inicio`, `fecha_fin`
- [ ] Ejecutar `php artisan db:seed` y verificar que no hay errores

**Problema:** Error 500 al guardar o cancelar en la vista de editar empresa.

---

## T3: Crear menú de convenios
**Prioridad:** Alta | **Estado:** Pendiente

- [ ] Añadir enlace "Convenios" en el sidebar (junto a Empresas)
- [ ] Crear `ConvenioController@index` para listar todos los convenios
- [ ] Crear vista `convenios/index.blade.php` con tabla de convenios
- [ ] Añadir rutas para index/show de convenios
- [ ] Los convenios existentes se crean correctamente al hacer seed con las nuevas columnas

**Problema:** Los convenios no aparecen en ningún sitio. Solo están anidados dentro de cada empresa.

---

## T4: Crear vista de gestión académica
**Prioridad:** Alta | **Estado:** Pendiente

Necesita una nueva sección de administración académica:

- [ ] Crear `AcademicStructureController` con:
  - `index()` — vista general de familias/ciclos/grupos
  - `familias()` — CRUD de familias
  - `ciclos()` — CRUD de ciclos (vinculados a familia)
  - `lineas()` — CRUD de líneas (mañana/tarde por ciclo)
  - `grupos()` — CRUD de grupos (vinculados a línea)
  - `asignaturas()` — CRUD de asignaturas (vinculadas a ciclo)
  - `tutores()` — asignar tutor a grupo
  - `profesores-grupos()` — asignar profesores a grupos (equipos educativos)
- [ ] Crear vistas blade para cada sección
- [ ] Añadir enlace en sidebar "Académico" o "Estructura"
- [ ] Crear migraciones si faltan columnas (tutor_id en grupos, etc.)

**Problema:** Hace falta una vista para definir familias, especialidades, cursos, asignar tutor de clase y profesores a grupos.

---

## T5: Arreglar colores en vistas (alumnos, profesores, empresas)
**Prioridad:** Media | **Estado:** Pendiente

- [ ] Revisar `resources/views/empresas/edit.blade.php` — cambiar `bg-gray-800` por `bg-white` y `text-white` por `text-slate-900`
- [ ] Revisar `resources/views/alumnos/edit.blade.php` — quitar `dark:` clases innecesarias
- [ ] Revisar `resources/views/profesores/edit.blade.php` — mismo problema
- [ ] Buscar todas las vistas con `bg-gray-800` que no sean layout y corregirlas
- [ ] Estándar: fondo claro (`bg-white`), texto oscuro (`text-slate-900`), labels (`text-slate-600`)

**Problema:** La vista de alumnos edit no se ve bien por el color del texto. Pasa en muchas vistas.

---

## T6: Arreglar seeder de alumnos (grupo_id → alumno_grupo)
**Prioridad:** Alta | **Estado:** Pendiente

- [ ] El seeder usa `grupo_id` en `Alumno::firstOrCreate()` pero esa columna ya no existe
- [ ] Cambiar a usar la tabla pivot `alumno_grupo`
- [ ] Verificar que el seeder de convenios usa las nuevas columnas

---

## T7: Arreglar relación `alumno->grupo()` eliminada
**Prioridad:** Alta | **Estado:** Pendiente

- [ ] `AnotacionController.php:136` usa `$alumno->grupo` — cambiar a `$alumno->grupos()->first()`
- [ ] Buscar cualquier otra referencia a `->grupo()` en el código
- [ ] Añadir relación `grupo()` como alias de `grupos()->first()` en el modelo Alumno si es necesario

**Problema:** Error `Call to undefined relationship [grupo] on model [App\Models\Alumno]` — la relación fue eliminada pero el código la sigue usando.

---

## T8: Actualizar tests
**Prioridad:** Alta | **Estado:** Pendiente

- [ ] Actualizar `AdminDashboardTest` — las prácticas finalizadas pueden ser 2 en vez de 1
- [ ] Actualizar `AlumnoModuleTest` — usar `grupos()` en vez de `grupo()`
- [ ] Actualizar `AnotacionModuleTest` — arreglar `$alumno->grupo`
- [ ] Actualizar `CalificacionModuleTest` — verificar relaciones
- [ ] Actualizar `CambioHistorialTest` — verificar relaciones
- [ ] Actualizar `EmpresaModuleTest` — arreglar `convenio` relationship y `ciclo_id`
- [ ] Actualizar `ProfesorModuleTest` — verificar que el store funciona
- [ ] Actualizar `ProyectoModuleTest` — verificar estadísticas
- [ ] Ejecutar `php artisan test` y verificar 252/252 passing

---

## T9: Actualizar especificaciones (SPEC.md)
**Prioridad:** Media | **Estado:** ✅ Completada

- [x] Actualizar RF2 — Estructura Académica (grupos muchos a muchos, tutor por grupo)
- [x] Actualizar RF3 — Alumnado (varios grupos, matrícula por ciclo)
- [x] Actualizar RF4 — Profesorado (especialidad, tutor de grupo, crear profesores)
- [x] Actualizar RF5 — Asignaturas y Grupos (líneas, grupos por línea, asignaturas a profesores)
- [x] Actualizar RF8 — Empresas (convenios con nuevas columnas, menú independiente)
- [x] Actualizar RF15 — Panel Admin (gestión estructura académica)
- [x] Actualizar RF16 — Historial de Cambios (sin cambios)
- [x] Añadir RF17 — Gestión de Estructura Académica (CRUD familias, ciclos, líneas, grupos, asignaturas)
- [x] Actualizar estructura de datos (tablas principales)
- [x] Añadir notas de implementación (tema visual, relaciones alumno-grupo, convenios)

---

## Orden de ejecución recomendado

1. **T7** — Arreglar relación `alumno->grupo()` (bloquea tests y anotaciones)
2. **T6** — Arreglar seeder (necesario para que los datos existan)
3. **T2** — Arreglar error 500 empresa edit (seeder roto)
4. **T5** — Arreglar colores en vistas (empresas, alumnos, profesores)
5. **T1** — Crear vistas de oferta de empleo
6. **T3** — Crear menú de convenios
7. **T4** — Crear vista de gestión académica
8. **T8** — Actualizar tests
9. **T9** — ✅ Completada