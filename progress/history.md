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

## 2026-08-29 — tutor-001 completado