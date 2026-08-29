
## 2026-08-29 — tutor-001 completado

- **Feature:** tutor-001 — Tutorías y Anotaciones
- **Modelo:** Anotacion (alumno_id, profesor_id, titulo, contenido, es_publica)
- **Controller:** AnotacionController (index filtrado, create, store, edit, update, destroy, show alumno)
- **Scopes:** paraAlumno, creadasPor, visiblesPara (profesor solo ve las suyas + públicas de otros)
- **Vistas:** anotaciones/index, create, edit, show — lista con filtros, detalle por alumno
- **Permisos:** Admin ve todo, Coordinador ve todo, Profesor solo sus anotaciones + públicas, Alumno no accede
- **Bugfix:** es_publica default false — añadido booted callback para prevenir null en SQLite
- **Tests:** 30 tests (56 assertions)
- **Total tests proyecto:** 130 passing / 130 total
- **Estado:** ✅ Completado
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

## 2026-08-29 — not-001 completado

- **Feature:** not-001 — Sistema de Notificaciones (Email + In-app)
- **Modelo:** Notificacion (tipo, titulo, mensaje, datos JSON, enlace, es_leida, expira_en)
- **Service:** NotificacionService con métodos tipados: empresaAsignada, acuerdoCambiado, proyectoCalificado, alumnoAsignado
- **Controlador:** NotificacionController (index con auto-mark-as-read, contador JSON)
- **Vista:** notificaciones/index.blade.php con iconos por tipo, paginación, diffForHumans
- **Email:** Plantilla emails/notificacion.blade.php
- **Navegación:** Enlace a notificaciones con badge de contador rojo
- **Expiración:** Notificaciones con expira_en configurable (días), limpieza automática
- **Tests:** 10 tests (ver, marcar leídas, contador, crear, empresa, acuerdo, calificación, expiradas, permanente, aislamiento)
- **Total tests proyecto:** 89 passing / 89 total

## 2026-08-29 — asig-001 completado

- **Feature:** asig-001 — Asignaturas, Grupos y Calificaciones
- **Modelo:** Calificacion (alumno_id, asignatura_id, evaluacion, nota decimal 0-10, observaciones)
- **Validaciones:** notaValida (0-10), escalarNota (SS/INS/SUF/B/SB/S → numérico), unique (alumno+asignatura+evaluacion)
- **Controlador:** CalificacionController (index filtrado, create, store, edit, update, destroy, show alumno)
- **Vistas:** calificaciones/{index,show,create,edit}.blade.php — tabla con notas coloreadas, medias por evaluación
- **Restricción RGPD:** Alumnos NO ven calificaciones. Profesor solo ve sus grupos tutor.
- **Navegación:** Enlace a Calificaciones para Admin/Coordinador/Profesor
- **Tests:** 11 tests (index, create, update, delete, profesor grupo, profesor no-grupo, alumno forbidden, show, duplicate, invalid nota, static methods)
- **Total tests proyecto:** 100 passing / 100 total
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
## 2026-08-29 18:41 — emp-001: Módulo Empresas

- Implementado CRUD de empresas (crear, ver, editar, eliminar)
- Tutores laborales (crear, actualizar, eliminar)
- Convenios Séneca (crear, actualizar estado firmado/no_firmado)
- Filtrado por búsqueda, familia, estado activo
- Control de acceso: solo Admin
- 23 tests de feature pasando (45 assertions)
- Arreglados: validación Laravel 10 (nullable_with no existe), unique constraint en convenios, factory de Convenio
## 2026-08-29 19:45 - prac-001 completado

- **prac-001**: Ofertas y Solicitud de Prácticas — 22/22 tests passing
- Se reconstruyeron las routes de ofertas en routes/web.php (se perdieron con git checkout)
- Se añadieron routes para: index, create, store, show, edit, update, destroy, mis-ofertas, postularse, solicitudes CRUD
- Se corrigió orden de routes: mis-ofertas antes de {oferta} para evitar 404
- Se añadieron imports de OfertaPracticaController y EmpresaController en web.php
- Se añadieron routes de empresas (CRUD + tutores laborales + convenios)
- features.json actualizado: prac-001 -> completed
- progress/current.json actualizado: current_feature -> prac-002
