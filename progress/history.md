
## 2026-08-30 — notif-001 completada

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
