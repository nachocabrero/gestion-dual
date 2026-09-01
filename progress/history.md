
## 2026-09-01 — Anotaciones: fix 500 al guardar y visibilidad siempre para el equipo educativo

### Fix error 500 al guardar
- Al crear una anotación con un usuario sin registro `profesor` (coordinador/admin), `profesor_id`
  quedaba a `null` y la vista `index` reventaba con `Attempt to read property "user" on null`.
- Vistas `index` y `show`: autor con null-safe `$a->profesor?->user?->name ?? '—'`.

### Visibilidad: sin público/privado
- Eliminada la columna `es_publica` de `anotaciones` (migración `2026_09_01_100000_...`).
- Eliminado el checkbox «Hacer pública» de las vistas create/edit y el badge pública/privada de index/show.
- Nuevo scope `Anotacion::visiblesPara($profesorId)`: el profesor ve sus anotaciones + las de alumnos
  de cualquier grupo al que imparte clase (todo el equipo educativo del alumno las ve, acorde a RF7).
- Controlador: `index` usa `$user->profesor?->id`; `store`/`update` ya no reciben `es_publica`.
- Modelo/factory/tests actualizados; test `es_publica_default_false` reemplazado por `persiste_entre_cursos`.
- SPEC.md RF7 actualizado.

### Validación
- `AnotacionModuleTest`: 30 tests passing.
- Suite completa: 250 passed, 2 fallos preexistentes ajenos
  (`AdminDashboardTest@dashboard_filters_convenios_by_familia`, `ProyectoModuleTest@portfolio_shows_statistics`).
- Migración aplicada en contenedor `ieshlanz-app` con `--force`; `view:clear`; `scripts/init.sh` OK.

## 2026-09-01 — Fix error 500 al crear anotación

- `AnotacionController`: se usaba la relación inexistente `Alumno::grupo()`. Sustituida por
  `grupos()` (belongsToMany) en `create` (línea 58) y en el eager loading de `index`
  (`alumno.grupos`). Las vistas ya usaban `$alumno->grupos`.
- Validación: `php artisan test --filter=AnotacionModuleTest` → 30 tests passing.

## 2026-08-31 19:40 — Mejora UX: crear grupos desde el nivel de ciclo

- El admin ahora puede añadir grupos directamente desde la vista de un ciclo con el botón
  «+ Nuevo Grupo» (`admin.estructura.grupos.create-ciclo`), eligiendo la línea/turno del ciclo
  además del número, curso académico y tutor.
- `GrupoController::createPorCiclo` / `storePorCiclo`: nuevo flujo que valida `linea_id` y
  crea el grupo en esa línea, redirigiendo al listado de grupos de la línea.
- Rutas nuevas: `admin.estructura.grupos.create-ciclo` (GET) y `...store-ciclo` (POST).
- La vista `grupos/create.blade.php` es compartida: muestra el selector de línea cuando se entra
  desde un ciclo y lo oculta en el flujo por línea.

### Validación
- `route:list` muestra ambas rutas de ciclo.
- Render de `createPorCiclo`: OK, contiene el selector «Línea / Turno».
- `storePorCiclo`: crea el grupo, redirige a `grupos.index` de la línea (302), y se limpia.
- `view:cache`, `php -l`: OK.


### Cambios
- **Controlador** `app/Http/Controllers/Admin/Estructura/CursoAcademicoController.php`:
  - `index`: lista cursos académicos con su estado (actual/anterior), nº de proyectos y acciones.
  - `create`/`store`: crea un nuevo curso académico (queda inactivo por defecto).
  - `setActive`: marca un curso como actual (único `is_active=true`, desactiva el resto).
  - `destroy`: elimina cursos no activos y sin proyectos.
- **Rutas** `admin.estructura.cursos.*` bajo el grupo admin/estructura (solo admin).
- **Vistas** `admin/estructura/cursos/index.blade.php` y `create.blade.php`.
- **Sidebar**: enlace «Cursos Académicos» en la sección Administración.
- Se ha marcado **2026-2027 como curso actual**, de modo que `Alumno::gruposActuales()`
  devuelve el grupo de 2º del curso en marcha.

### Validación
- `route:list` muestra las 5 rutas de cursos.
- Render del index de cursos: OK (contiene «Hacer actual»).
- `setActive` deja 2026-2027 como único activo; `gruposActuales()` del alumno 5 → «2º DAW - Tarde».
- `view:cache`, `php -l`: OK.


### Contexto
Preocupación del cliente sobre el cambio de curso académico: los alumnos de 1º pasan a 2º
cada curso, de forma transparente, cómoda y entendible, pudiendo consultar cursos anteriores.

### Cambios
- **Migración** `2026_08_31_190127_add_curso_academico_to_alumno_grupo_table.php`:
  añade `curso_academico_id` (nullable, FK→cursos_academicos) a `alumno_grupo` para versionar
  la pertenencia alumno→grupo por curso. Mantiene la unique `(alumno_id, grupo_id)`. Backfill
  desde `grupo.curso_academico_id`.
- **Modelos**:
  - `Alumno`: `grupos()` con pivot `curso_academico_id`; nuevos `gruposEnCurso($cursoId)` y `gruposActuales()`.
  - `Grupo`: `alumnos()` con pivot; nuevo `alumnosEnCurso($cursoId)`.
- **Comando** `app/Console/Commands/PromocionAnual.php` (`academico:promocion-anual`):
  - `--preview` para revisar el plan sin aplicar.
  - Aplica: congela el estado actual en el curso origen, backfillea pivots, crea/usa el curso
    destino, crea grupos homólogos (misma línea, número+1) si no existen, mueve alumnos,
    actualiza matrículas y registra en el historial de cambios.
  - Idempotente: no vuelve a promocionar a quien ya tiene grupo en el curso destino.
  - Probado: 7 alumnos promocionados, 5 grupos de destino creados (datos de desarrollo).
- **Scheduler** `app/Console/Kernel.php`: tarea anual el 1 de agosto a las 00:00 que avisa
  para revisar y aplicar la promoción (registrada en `schedule:list`).
- **Filtro por curso académico** en `AlumnoController@index` y `alumnos/index.blade.php`
  (selector "Todos los cursos"), permitiendo consultar cursos anteriores.

### Validación
- Render del listado de alumnos con `?curso_academico_id=N`: OK.
- `view:cache`, `schedule:list`, `php -l` de archivos modificados: OK.
- `scripts/init.sh`: todo OK.
- Conteo por filtro: 7 alumnos en curso 2026-2027 (promocionados), 19 en 2025-2026.


- Integración de notificaciones con eventos:
  - `OfertaPracticaController::aceptar()` → notifica empresa asignada
  - `ProyectoController::calificar()` → notifica proyecto calificado
  - `PracticaController::actualizarHoras()` → notifica convenio firmado
- Cron job diario a las 3:00 para limpieza de notificaciones expiradas
- 2 tests nuevos de integración (aceptar solicitud, calificar proyecto)
- Total tests: 230 passing
## 2026-08-30 — proj-001 completado

- **Feature:** proj-001 — Módulo de Proyectos (2º)
- **Modelo:** Proyecto (alumno_id, ciclo_id, curso_academico_id, titulo, descripcion, enlace_repositorio, enlace_despliegue, calificacion, es_destacado, destacado_por_id)
- **Modelo:** ProyectoImagen (proyecto_id, url)
- **Controller:** ProyectoController (index, create, store, show, edit, update, destroy, calificar, portfolio)
- **Scopes:** destacados, porCiclo, porCurso
- **Vistas:** proyectos/index, create, edit, show, portfolio + layout público
- **Permisos:** Admin ve todo, Profesor solo su grupo, Alumno solo sus proyectos (sin calificación), Portfolio público sin auth
- **Validaciones:** 300 palabras máx descripción, calificación 1-10, imágenes máx 5MB
- **Portfolio público:** agrupado por ciclo, filtros por nombre/ciclo, estadísticas (total proyectos, empresas, nota media, destacados)
- **Tests:** 26 tests (48 assertions) — 20 de módulo + 6 de portfolio
- **Total tests proyecto:** 226 passing / 226 total
- **Estado:** ✅ Completado

## 2026-08-30 — proj-002 completado

- **Feature:** proj-002 — Portfolio Público (mejoras)
- **Mejoras implementadas:**
  - ✅ Filtro por curso académico añadido al portfolio
  - ✅ Logo SVG del IES Hermenegildo Lanz (public/images/logo.svg)
  - ✅ Layout público con logo + nombre del instituto
  - ✅ Filtros: nombre, ciclo, curso académico (3 filtros combinables)
  - ✅ 2 tests nuevos: filtro por curso, umbral calificación mínima 7/10
- **Tests:** 28 tests (52 assertions) — 2 nuevos añadidos
- **Total tests proyecto:** 228 passing / 228 total
- **Estado:** ✅ Completado

## 2026-08-29 — tutor-001 completado
## 2026-08-30 09:55 - admin-001: Panel de Administración

- **Dashboard** con métricas: usuarios (alumnos/profesores/empresas activos e inactivos)
- **Prácticas**: conteo por estado (pendientes, en curso, finalizadas)
- **Convenios**: firmados/no firmados con filtros por familia y curso académico
- **Proyectos destacados**: agrupados por ciclo con calificaciones
- **Ciclos formativos**: conteo de alumnos activos por ciclo
- **Actividad reciente**: últimas notificaciones del sistema
- **Navegación**: enlace al admin panel en la barra de navegación para admins
- **Seguridad**: middleware de rol admin protege todas las rutas
- **Tests**: 10 tests de cobertura completa
- **Total tests suite**: 240 passing
## 2026-08-30 09:55 - hist-001: Historial de Cambios

- **Estado**: Completado
- **Tests**: 12 nuevos tests, 252 passing total
- **Cambios**:
  - Migration: create_cambios_table (tabla genérica cambios)
  - Model: Cambio con relaciones usuario y registrable (morphTo)
  - Trait: RegistrableCambio - registra automaticamente created/updated/deleted/estado_cambiado
  - Controller: CambioController con index (filtros) y show (detalle antes/despues)
  - Views: admin/cambios/index.blade.php y admin/cambios/show.blade.php
  - Rutas: admin/cambios y admin/cambios/{cambio} en grupo admin
  - Trait aplicado a 13 modelos: User, Alumno, Profesor, Empresa, Convenio, Proyecto, Practica, OfertaPractica, SolicitudPractica, Ciclo, Grupo, Calificacion, Anotacion
  - Factory: CambioFactory
  - Navegacion: enlace "Historial" en sidebar para admins
## 2026-08-30 19:25 — ui-002: Banner de Cookies RGPD/LOPDGDD

- **Banner de cookies** con 3 opciones: aceptar todas, solo necesarias, configurar
- **Panel de configuración** con toggle para cookies de preferencias
- **Integrado** en app layout y guest layout (todas las páginas)
- **CookieController** actualizado para JSON responses en AJAX
- **Página de cookies** existente con contenido completo (7 secciones)
- **Privacy/Aviso legal** con datos completos del IES Hermenegildo Lanz
- **Registro de consentimiento** en DB (consent_cookies_at) y session para no logueados
- **Tests**: 252 passing / 252 total
## 2026-08-31 09:40 — Fix errores 500 y docencia grupo+asignatura

### Fix errores 500 (alumnos/profesores)
- `alumnos/edit.blade.php`: accesos null-safe a `linea->ciclo->codigo` y `matricula->ciclo` (líneas 62 y 90)
- `alumnos/show.blade.php`: fechas del pivot `alumno_ciclo_matricula` ahora como Carbon
- `app/Models/AlumnoCicloMatricula.php`: extendido de `Pivot` con casts `datetime` para `matriculado_at`/`graduado_at`
- `app/Models/Alumno.php`: `ciclosMatriculados()` usa `->using(AlumnoCicloMatricula::class)`
- `profesores/edit.blade.php`: substituida relación inexistente `User::tutorias()` por `Profesor::gruposTutor()`

### Docencia grupo + asignatura (spec deviation)
- Migración `2026_08_31_093000_add_asignatura_to_grupo_profesor_table`: añade `asignatura_id` (nullable) al pivot `grupo_profesor`; unique `(grupo_id, profesor_id, asignatura_id)`
- `Profesor::gruposImpartidos()` y `Grupo::profesores()` con `withPivot('asignatura_id')`
- `ProfesorController`: `pivotGruposAsignaturas()` construye `[grupo_id => asignatura_id]`; store/update la aplican
- Vistas `create`/`edit`: selector por grupo (checkbox + desplegable de asignatura)
- Vista `show`: sección "Grupos que imparte" con la asignatura de cada grupo
- Seeder actualizado para asignar asignatura en los grupos
- SPEC.md actualizado (RF2, RF4, modelo grupo_profesor)

## 2026-08-31 12:15 — Módulo Estructura Académica y vínculo Profesor-Familia

### Módulo Estructura Académica (Panel Admin)
- Nuevo grupo de rutas `admin.estructura.*` bajo prefijo `admin/estructura`
- Controladores en `app/Http/Controllers/Admin/Estructura/`: FamiliaController, CicloController, LineaController, GrupoController, AsignaturaController
- Vistas en `resources/views/admin/estructura/`: familias/ (index, create, edit, show), ciclos/ (index, create, edit, show), lineas/ (index, create, edit, show), grupos/ (index, create, edit, show), asignaturas/ (index, create, edit)
- Árbol de navegación: Familias -> Ciclos -> Líneas/Turnos -> Grupos; Asignaturas por ciclo
- Enlace "Estructura Académica" en dashboard admin y menú desplegable del Admin (navigation)
- Requiere route model binding para Familia, Ciclo, Linea, Grupo, Asignatura; rutas específicas declaradas antes de las genéricas
- Validado: `route:list` muestra todas las rutas; `view:cache` compila todas las plantillas; render OK de las 8 vistas de estructura

### Vínculo Profesor ↔ Familia
- Migración `2026_08_31_093500_add_familia_id_to_profesores_table` (aplicada con --force): FK `familia_id` nullable -> familias
- `Profesor`: `familia_id` en `$fillable` + relación `familia()`
- `ProfesorController`: validación y persistencia de `familia_id` en store/update; `$familias` a create/edit; carga eager `familia` en show
- Vistas profesores/create y edit: select de Familia profesional; show: muestra familia
- SPEC.md y progress actualizados

### Nota sobre validación
- Los tests feature preexistentes contra el MySQL compartido siguen fallando por datos de seeder duplicados (preexistente, ajeno a estos cambios). Validación fiable: render de vistas + route:list + view:cache.

## 2026-09-01 — Ofertas de Prácticas: dirigir a varios grupos

- Migración `2026_09_01_000001_create_grupo_oferta_table.php`: pivot `grupo_oferta` (grupo_id, oferta_practica_id, unique conjunta) para vincular una oferta a varios grupos clase.
- `OfertaPractica`: nueva relación `grupos()` (BelongsToMany) vía pivot `grupo_oferta`.
- `OfertaPracticaController::create`: pasa `$grupos` (grupos activos) a la vista. `store`: validación `grupo_ids` (array, exist en grupos) y `sync` al crear.
- Vista `ofertas/create`: select múltiple de grupos a los que va dirigida la oferta.
- Vistas `ofertas/index` y `ofertas/show`: muestran los grupos como badges; el controlador carga `grupos` en index y show (eager load).
- Nuevo test `test_profesor_can_create_oferta_with_grupos`.
- Validado: 23 tests de OfertaPracticaTest en verde, `view:cache` compila las plantillas.

## 2026-09-01 — Ofertas: grupos del curso actual + edición; Prácticas: horas acumulables

### Ofertas de Prácticas → solo grupos del curso actual
- `OfertaPracticaController`: nuevo helper privado `gruposCursoActual()` que devuelve los grupos activos del curso académico activo (`CursoAcademico::active()->orderBy('fecha_inicio','desc')->first()`), siguiendo el patrón de ProfesorController/LineaController.
- `create` y `edit` usan `gruposCursoActual()` en lugar de todos los grupos activos.

### Oferta: edición con grupos (antes solo en el alta)
- `OfertaPracticaController::edit`: ahora pasa `$grupos` a la vista.
- `OfertaPracticaController::update`: valida `grupo_ids` (array, exists) y hace `sync` para poder sustituir los grupos destinatarios.
- Nueva vista `resources/views/ofertas/edit.blade.php` con select múltiple de grupos (pre-selecciona los actuales de la oferta), estado, etc.
- `show`: pasa `$thisCanEdit` y muestra botón "Editar" para creador/admin.
- Tests: `test_profesor_can_edit_oferta_y_actualizar_grupos` y `test_create_view_solo_muestra_grupos_del_curso_actual`.

### Fix error 500 al guardar oferta
- La migración `2026_09_01_000001_create_grupo_oferta_table` no estaba aplicada en la BD real (MySQL), causando `Table 'ieshlanz.grupo_oferta' doesn't exist` en `OfertaPracticaController@store` al hacer `sync`.
- Aplicada con `docker compose exec app php artisan migrate --force` (batch 12).

### Prácticas: horas acumulables entre 1º y 2º
- `Practica::validarMinimoHoras` → `validarDatos`: se elimina el bloqueo de "primera práctica del curso < 500h". Cada práctica puede tener cualquier número de horas; la restricción es la suma total.
- Nuevo `Practica::totalHorasAlumno()`: suma `horas_acumuladas` de todas las prácticas del alumno.
- `PracticaController::store/update`: usan `validarDatos` y `redirigirTrasGuardar()`, que avisa (warning no bloqueante) cuando el total acumulado aún no supera las 500h.
- Vista `practicas/create`: texto de ayuda actualizado (varias prácticas entre 1º y 2º, suma total ≥ 500h).
- SPEC.md RF11 y features.json actualizados.
- Test: `test_practica_permite_horas_por_debajo_de_500_acumulando` (2 prácticas → 300h + 250h = 550h, sin warning en la segunda).

### Validación
- `tests/Feature/OfertaPracticaTest` (25) y `PracticaModuleTest` (21) en verde. `./scripts/init.sh` OK.
- Fallos preexistentes (ajenos a estos cambios): `AdminDashboardTest > dashboard filters convenios by…` y `ProyectoModuleTest > portfolio shows statistics`.

## 2026-09-01 — Ofertas: enviar a los alumnos de los grupos destinatarios

- Nueva acción `POST /ofertas/{oferta}/enviar` → `OfertaPracticaController@enviarAAlumnos` (ruta `ofertas.enviar`).
- Permisos: creador/admin o roles profesor/coordinador dual.
- Requiere que la oferta dirija al menos un grupo (`grupo_oferta`); si no, error de validación.
- Busca los alumnos de los grupos destinatarios en el curso académico activo (vía pivot `alumno_grupo.curso_academico_id`, incluye filas legacy con NULL) y con rol alumno.
- Envía notificación in-app + email por alumno (`NotificacionService::ofertaEnviada`, tipo `oferta_nueva`, enlace al detalle de la oferta).
- Si la oferta estaba `pendiente`, pasa a `activa` (para que los alumnos puedan postularse).
- Botón "Enviar a alumnos" / "Volver a enviar a alumnos" en `ofertas/show` para quien puede editarla (no en ofertas cerradas).
- Tests: `test_profesor_puede_enviar_oferta_a_alumnos_de_los_grupos` (notifica a cada alumno y activa la oferta) y `test_profesor_no_puede_enviar_oferta_sin_grupos`.
- Validado: 27 tests OfertaPracticaTest + PracticaModuleTest en verde, `view:cache` OK, `init.sh` OK. Fallos preexistentes ajenos: AdminDashboardTest y ProyectoModuleTest portfolio.

## 2026-09-01 — Ofertas: envío selectivo (todos los grupos o alumnos concretos)

- Nueva pantalla `GET /ofertas/{oferta}/enviar` → `OfertaPracticaController@enviarForm` (ruta `ofertas.enviar-form`).
- La vista `ofertas/enviar` lista los grupos activos del curso actual con sus alumnos (del curso actual, rol alumno). Marca como "Grupo destinatario" los grupos a los que ya va dirigida la oferta.
- Selección flexible con Alpine.js: botones "Seleccionar todos los grupos" / "Ninguno", checkbox por grupo (marca/desmarca todos sus alumnos) y checkbox por alumno individual. Contador de seleccionados.
- `POST /ofertas/{oferta}/enviar` (ruta `ofertas.enviar`) ahora valida `alumno_ids[]` explícito y notifica únicamente a los alumnos seleccionados; activa la oferta si estaba pendiente.
- El botón en `ofertas/show` ("Enviar a alumnos" / "Volver a enviar") ahora enlaza a la pantalla de envío y se muestra también al profesor/coordinador (nuevo `canEnviarOferta`).
- Tests: envío a todos, envío solo a un alumno concreto (el otro no recibe), validación sin selección, y formulario solo muestra grupos del curso actual. 29 tests de OfertaPracticaTest en verde.

## 2026-09-01 (bis) — Envío solo a grupos destinatarios + rediseño detalle de oferta

- `enviarForm` ahora filtra únicamente los grupos a los que va dirigida la oferta (`grupo_oferta`); si no tiene grupos, la pantalla avisa y enlaza a edición para asignarlos. Se eliminó el badge "Grupo destinatario" y la preselección marca todos los alumnos (todos son elegibles).
- Rediseño de `ofertas/show`: detalle con cabecera (especialidad + estado), tarjetas de Empresa / Nº alumnos / Creador / Fecha, grupos destinatarios, y botones diferenciados: "Enviar a alumnos" sólido (#0048FE) y "Editar oferta" con borde. Tabla de solicitudes y vacíos en Tailwind.
- Tests: formulario solo muestra grupos destinatarios (31 tests de OfertaPracticaTest en verde).

## 2026-09-02 — Empresas: sin convenios en el módulo + ofertas y prácticas por curso académico

- Migración `2026_09_02_000000_add_curso_academico_to_ofertas_practicas_table.php`: columna `curso_academico_id` nullable en `ofertas_practicas` + backfill por `fecha_inicio` del curso (el último con `fecha_inicio <= created_at`; si no, el curso activo).
- `OfertaPractica`: `curso_academico_id` en `$fillable`, relación `cursoAcademico()`, y hook `booted` que asigna el curso activo al crearse si viene vacío.
- `EmpresaController`: se elimina toda la lógica de convenios de `store` y `update`; `index` deja de pasar convenios; `create`/`edit` dejan de cargar ciclos/convenios. Nuevo `show` que carga ofertas y prácticas de la empresa y las agrupa por curso académico (`agruparPorCurso`): el curso actual va primero (aunque esté vacío) y los datos sin curso se agrupan en un bloque marcado como actual.
- Vistas: `empresas/index` sin columna ni filtro de convenios (nuevas columnas Ofertas/Prácticas); `empresas/create` y `empresas/edit` sin bloque de convenios; `empresas/show` con sección "Ofertas y Prácticas" por curso: el curso actual desplegado por defecto y los anteriores plegados (Alpine `x-data`/`x-show`, sin plugin Collapse).
- Sidebar: "Empresas y Convenios" → "Empresas". SPEC RF8 actualizado (convenios fuera del módulo de empresas; detalle por curso académico con actual desplegado).
- Tests: se eliminan los asserts de convenios del create/update de empresa y el test `test_admin_can_update_convenio`; 2 nuevos tests de agrupación por curso (`test_admin_can_view_show_with_ofertas_y_practicas_del_curso_actual_y_anterior`, `test_empresa_show_agrupa_sin_curso_como_actual`).
- Validado: 24 tests EmpresaModuleTest en verde, suite completa con solo los 2 fallos preexistentes (`AdminDashboardTest > dashboard filters convenios by…`, `ProyectoModuleTest > portfolio shows statistics`), `init.sh` OK. Convenios se mantienen en BD/modelo/panel admin fuera del módulo de empresas.
