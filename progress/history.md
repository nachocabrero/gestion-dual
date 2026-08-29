# Historial de Cambios — IESH Lanz

## 2026-08-29 — acad-001 completado

- **Feature:** acad-001 — Estructura académica

## 2026-08-29 — alum-001 completado

- **Feature:** alum-001 — Módulo Alumnado (CRUD completo)
- **Controlador:** `AlumnoController` con index, show, create, store, update, deactivate, reactivate, destroy
- **Vistas:** `alumnos/index.blade.php`, `show.blade.php`, `create.blade.php`, `edit.blade.php`
- **Rutas:** Resource routes con middlewares de autorización (Policies)
- **RGPD:** Consentimiento automático al crear alumno, campos `consent_rgpd` y `consent_rgpd_at`
- **Autorización:** Admin ve todo, profesor solo su grupo, alumno solo su perfil
- **Tests:** 10 tests pasando (index, create, edit, deactivate, reactivate, delete, profesor scope, alumno scope, no-admin, RGPD)
- **Total tests proyecto:** 69 passing / 69 total

## 2026-08-29 — prof-001 completado

- **Feature:** prof-001 — Módulo Profesorado (CRUD completo)
- **Modelos:** Profesor, Asignatura, Sustitucion (con `$table` explícito para español)
- **Controlador:** `ProfesorController` con index, show, create, store, update, deactivate, destroy, sustituciones
- **Vistas:** `profesores/{index,show,create,edit}.blade.php`
- **Policies:** `ProfesorPolicy` + Gates (view, create, update, delete, deactivate)
- **Relaciones:** Profesor→Asignaturas (many-to-many), Profesor→Grupos (equipos educativos, many-to-many), Profesor→Sustituciones
- **Seeder:** `AcademicStructureSeeder` actualizado con asignaturas por ciclo
- **Tests:** 10 tests pasando (index, create, edit, deactivate, delete, ver perfil, ver otros, no-admin, RGPD, asignaturas)
- **Total tests proyecto:** 79 passing / 79 total
- **Estado:** ✅ Completado
- **Modelos:** Familia, Ciclo, Linea, Grupo, Alumno
- **Relaciones:** Familia→Ciclos→Lineas→Grupos→Alumnos
- **Matrícula múltiple:** Alumno puede estar en varios ciclos a la vez (pivot con curso académico)
- **Seeder:** DAW, DAM, ASIR con líneas mañana/tarde y grupos con tutores
- **Tests:** 8 tests (20 assertions)
- **Total tests:** 59 passed (137 assertions)
- **Commit:** `5b2649a`

---

## 2026-08-29 — auth-001 completado

- **Feature:** auth-001 — Autenticación y roles
- **Estado:** ✅ Completado
- **Tests:** 51 passed (117 assertions)
- **Seguridad:** RGPD (consentimiento, supresión, rectificación), CSRF, policies, middlewares de rol y estado
- **Admin:** Panel de gestión de usuarios (activar/desactivar/eliminar)
- **Commit:** `63c6c80`

---

## Estado Inicial

- **Fecha:** 2026-08-29
- **Estado:** Proyecto iniciado
- **SPEC.md:** Creado con 16 requisitos funcionales y 7 no funcionales
- **Features:** 14 features definidas
- **Tareas completadas:** 2 de 14