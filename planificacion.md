# Planificación — Correcciones y Nuevas Funcionalidades

## Resumen de problemas

1. **Error 500 al crear oferta de empleo** — falta la vista `ofertas/create.blade.php`
2. **Error 500 al editar empresa (guardar/cancelar)** — se debe a que el seeder intenta insertar `ciclo_id` y `curso_academico` en convenios, pero esas columnas ya no existen
3. **Convenios no aparecen en ningún sitio** — no hay menú de convenios, solo están anidados dentro de cada empresa
4. **Falta vista de familias/especialidades/cursos** — no hay UI para gestionar familias, ciclos, líneas, grupos, asignaturas, tutores de grupo
5. **Vista alumnos edit no se ve bien (color texto)** — las vistas de empresas usan tema oscuro (`bg-gray-800`, `text-white`) sobre layout claro
6. **Profesor edit no se ve bien** — mismo problema de color
7. **Admin no puede crear profesores** — el botón existe pero puede que la ruta no sea accesible o haya error
8. **25 tests fallidos** — por la migración de `grupo_id` → `alumno_grupo` y convenios con columnas eliminadas

---

## TAREAS

### T1: Crear vistas de oferta de empleo (create + edit)
**Prioridad:** Alta  
**Estado:** Pendiente

- [ ] Crear `resources/views/ofertas/create.blade.php`
- [ ] Crear `resources/views/ofertas/edit.blade.php`
- [ ] Verificar que `OfertaPracticaController` pasa las variables correctas
- [ ] Verificar que las rutas `ofertas.create` y `ofertas.edit` existen

### T2: Arreglar error 500 en empresa edit (guardar/cancelar)
**Prioridad:** Alta  
**Estado:** Pendiente

- [ ] Investigar el error exacto (ver logs)
- [ ] El seeder `IesDataSeeder` intenta insertar `ciclo_id` y `curso_academico` en `convenios` — esas columnas ya no existen
- [ ] Actualizar el seeder para usar las nuevas columnas de convenio: `alumno_id`, `grupo_id`, `tutor_laboral_id`, `tutor_docente_id`, `numero_horas`, `fecha_inicio`, `fecha_fin`
- [ ] Ejecutar `php artisan db:seed` y verificar que no hay errores

### T3: Crear menú de convenios
**Prioridad:** Alta  
**Estado:** Pendiente

- [ ] Añadir enlace "Convenios" en el sidebar (junto a Empresas)
- [ ] Crear `ConvenioController@index` para listar todos los convenios
- [ ] Crear vista `convenios/index.blade.php` con tabla de convenios
- [ ] Añadir rutas para index/show de convenios
- [ ] Los convenios existentes se crean correctamente al hacer seed con las nuevas columnas

### T4: Crear vista de gestión académica (familias, ciclos, grupos, asignaturas, tutores)
**Prioridad:** Alta  
**Estado:** Pendiente

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

### T5: Arreglar colores en vistas (alumnos, profesores, empresas)
**Prioridad:** Media  
**Estado:** Pendiente

- [ ] Revisar `resources/views/empresas/edit.blade.php` — cambiar `bg-gray-800` por `bg-white` y `text-white` por `text-slate-900`
- [ ] Revisar `resources/views/alumnos/edit.blade.php` — quitar `dark:` clases innecesarias
- [ ] Revisar `resources/views/profesores/edit.blade.php` — mismo problema
- [ ] Buscar todas las vistas con `bg-gray-800` que no sean layout y corregirlas
- [ ] Estándar: fondo claro (`bg-white`), texto oscuro (`text-slate-900`), labels (`text-slate-600`)

### T6: Arreglar seeder de alumnos (grupo_id → alumno_grupo)
**Prioridad:** Alta  
**Estado:** Pendiente

- [ ] El seeder usa `grupo_id` en `Alumno::firstOrCreate()` pero esa columna ya no existe
- [ ] Cambiar a usar la tabla pivot `alumno_grupo`
- [ ] Verificar que el seeder de convenios usa las nuevas columnas

### T7: Arreglar relación `alumno->grupo()` eliminada
**Prioridad:** Alta  
**Estado:** Pendiente

- [ ] `AnotacionController.php:136` usa `$alumno->grupo` — cambiar a `$alumno->grupos()->first()`
- [ ] Buscar cualquier otra referencia a `->grupo()` en el código
- [ ] Añadir relación `grupo()` como alias de `grupos()->first()` en el modelo Alumno si es necesario

### T8: Actualizar tests
**Prioridad:** Alta  
**Estado:** Pendiente

- [ ] Actualizar `AdminDashboardTest` — las prácticas finalizadas pueden ser 2 en vez de 1
- [ ] Actualizar `AlumnoModuleTest` — usar `grupos()` en vez de `grupo()`
- [ ] Actualizar `AnotacionModuleTest` — arreglar `$alumno->grupo`
- [ ] Actualizar `CalificacionModuleTest` — verificar relaciones
- [ ] Actualizar `CambioHistorialTest` — verificar relaciones
- [ ] Actualizar `EmpresaModuleTest` — arreglar `convenio` relationship y `ciclo_id`
- [ ] Actualizar `ProfesorModuleTest` — verificar que el store funciona
- [ ] Actualizar `ProyectoModuleTest` — verificar estadísticas
- [ ] Ejecutar `php artisan test` y verificar 252/252 passing

### T9: Actualizar especificaciones (SPEC.md)
**Prioridad:** Media  
**Estado:** Pendiente

- [ ] Actualizar `SPEC.md` con las nuevas funcionalidades:
  - Menú de convenios independiente
  - Gestión de estructura académica (familias, ciclos, líneas, grupos, asignaturas)
  - Asignación de tutores a grupos
  - Asignación de profesores a grupos
  - Alumnos en múltiples grupos
  - Creación de profesores desde admin
  - Vistas con tema claro consistente

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
9. **T9** — Actualizar especificaciones