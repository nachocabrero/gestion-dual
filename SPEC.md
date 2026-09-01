# SPEC.md — Gestión Académica IES Hermenegildo Lanz

## Visión General

Aplicación web para la gestión académica del **IES Hermenegildo Lanz (Granada)**, empezando por el departamento de Informática. Escalable a 2000-3000 alumnos.

La aplicación gestiona:
- Datos de alumnado y profesorado
- Asignaturas, grupos y calificaciones
- Tutorías y anotaciones
- Prácticas con empresas (ofertas, solicitudes, asignaciones)
- Módulo de proyectos de 2º (repositorio, despliegue, portfolio público)
- Notificaciones (email + in-app)
- Panel de administración con estadísticas
- Historial de cambios (antes/después)

---

## Stack Técnico

- **Backend:** Laravel 10 (PHP 8.1+)
- **Frontend:** Blade templates + Alpine.js + Tailwind CSS
- **Base de datos:** MySQL (producción), SQLite (desarrollo)
- **Autenticación:** Laravel Breeze/Jetstream (email + contraseña)
- **Notificaciones email:** Laravel Mailables (SMTP)
- **Notificaciones in-app:** Tabla `notifications` + Laravel Notifications
- **Almacenamiento imágenes:** Laravel Storage (local/public o S3)
- **Deploy:** Docker en Oracle free tier

---

## Requisitos Funcionales

### RF1 — Autenticación y Roles
- Login con email + contraseña
- Roles: Alumno, Profesor, Coordinador Dual, Empresa, Admin
- Un usuario puede tener múltiples roles
- Profesores usan email @ieshlanz.es, alumnos email personal, empresas email propio
- Admin puede desactivar/reactivar usuarios (con registro en historial)

### RF2 — Estructura Académica
- Familias (estándar Ministerio) → Ciclos (DAW, DAM, SMR, Informática Básica...) → Líneas (mañana/tarde) → Grupos (1ºA, 1ºB...)
- Grados: Básica, Media, Superior, Especialización, Acreditación
- Un alumno puede estar en varios grupos (puede pertenecer a más de un grupo)
- Un alumno puede estar en varios ciclos a la vez (matrícula por ciclo)
- Todo organizado por curso académico (26/27, 27/28...)
- Turnos solo a nivel agrupación de alumnos
- Cada grupo tiene un tutor asignado (profesor)
- Los grupos se organizan por línea (mañana/tarde) y ciclo
- Las asignaturas se imparten por profesor en cada grupo (docencia grupo+asignatura)
- Los profesores pueden impartir a varios grupos (equipos educativos), eligiendo qué asignatura dan en cada grupo

### RF3 — Alumnado
- Datos personales: nombre, email, teléfono, dirección...
- Enlace a LinkedIn
- Puede pertenecer a varios grupos (relación muchos a muchos)
- Puede estar matriculado en varios ciclos a la vez
- Tutor del grupo asignado
- Tutor de prácticas asignado
- Estado: activo/inactivo

### RF4 — Profesorado
- Datos personales: nombre, email @ieshlanz.es, teléfono...
- Especialidad
- Equipos educativos (a qué grupos da clase, con la asignatura que imparte en cada uno)
- Asignaturas que imparte
- Puede ser tutor de un grupo (tutor de aula)
- Puede ser coordinador dual
- Sustitutos: definir quién sustituye y periodo (abierto)
- Roles: profesor, coordinador dual, admin
- Admin puede crear nuevos profesores

### RF5 — Asignaturas y Grupos
- Asignaturas por ciclo
- Líneas (mañana/tarde) por ciclo
- Grupos por línea (ej: 1º DAW Mañana A, 1º DAW Mañana B)
- Cada grupo tiene un tutor asignado (profesor)
- Los profesores se asignan a grupos (equipos educativos)
- Las asignaturas se asignan a profesores
- Horarios de clase (pendiente)

### RF6 — Calificaciones
- Por evaluación (1ª, 2ª, 3ª) y/o módulo
- Calificación numérica
- Los alumnos NO ven sus calificaciones
- Cada profesor solo ve calificaciones de sus grupos

### RF7 — Tutorías y Anotaciones
- Anotaciones por alumno (ej. "Juan es bueno en BD")
- Pueden crear: tutor del grupo + coordinador Dual (+ Admin)
- Se mantienen entre cursos
- El tutor puede ver anotaciones de cualquier clase a la que dé
- Las anotaciones son visibles siempre para todo el equipo educativo del alumno:
  todos los profesores que imparten clase en alguno de los grupos del alumno pueden verlas (no existe publicar/privado)
- Editables con registro en historial

### RF8 — Empresas
- Datos fiscales: nombre, CIF, dirección, teléfono, email
- Responsable: nombre + DNI
- Tutores laborales: uno o varios (fijos por empresa, siempre de la empresa)
- Convenios: por alumno, grupo, tutor laboral, tutor docente, horas, fechas. No forman parte del módulo de empresas: se gestionan desde el panel de administración y a nivel de las prácticas (`convenio_firmado`)
- Filtrable por familia, especialidad, curso
- Detalle de empresa: muestra las ofertas de prácticas y las prácticas de la empresa agrupadas por curso académico
- Curso académico actual: visible (desplegado) por defecto
- Cursos académicos anteriores: plegados, se despliegan a petición del usuario

### RF9 — Ofertas de Prácticas
- Creadas por profesor o empresa
- Especifica: especialidad requerida, nº de alumnos necesarios
- El profesor dirige la oferta a alumnos concretos (filtra por especialidad)
- La oferta puede dirigirse a uno o varios grupos clase (relación muchos a muchos vía `grupo_oferta`)
- El profesor/creador puede enviar la oferta a los alumnos: solo se muestran los alumnos de los grupos a los que está dirigida la oferta (si no tiene grupos, se le pide que los asigne antes), pudiendo enviarla a todos ellos o solo a los que seleccione. El envío activa la oferta y notifica (in-app + email) a los alumnos seleccionados

### RF10 — Solicitud y Asignación de Prácticas
- Alumno ve la oferta → puede postularse
- Profesor/Coordinador acepta/rechaza
- Estado: Pendiente → Aceptado / Rechazado
- Alumno puede rechazar antes de que el convenio esté firmado
- Alumno aceptado ve: datos empresa, tutor laboral, fechas, etc.

### RF11 — Historial de Horas
- No hay mínimo de horas por práctica: se pueden registrar varias prácticas entre 1º y 2º
- La restricción es que la suma total de todas las prácticas del alumno supere las 500h
- La gestión real de horas se hace por otra plataforma
- Aquí solo se registra el acumulado por curso

### RF12 — Módulo de Proyectos (solo 2º)
- Obligatorio para todos los ciclos de 2º
- Alumno sube: enlace repositorio, enlace proyecto desplegado, título, descripción (máx. 300 palabras), capturas/imágenes
- Calificación numérica impartida por un único profesor del módulo
- Alumno puede editar hasta que se le pone la calificación
- Alumnos NO ven sus calificaciones
- Profesor solo ve proyectos del grupo al que imparte
- Profesor marca proyectos como "destacados"

### RF13 — Portfolio Público
- Visible para cualquier persona (sin login)
- Muestra proyectos destacados por curso académico
- Proyectos agrupados por ciclo (DAW, DAM, SMR, Informática Básica...)
- Filtros: por nombre, ciclo, curso académico
- Muestra: nombre del alumno, título del proyecto, descripción, imágenes, enlaces
- Logo del IES Hermenegildo Lanz en toda la web
- Estadísticas públicas: nº de empresas colaboradoras (sin nombres), otras estadísticas positivas

### RF14 — Notificaciones
- Email + In-app
- Sin configuración por parte del alumno (todo activo)
- In-app: icono con contador de no leídas → lista al pulsar
- Cada notificación puede tener enlace al suceso
- Se marcan como leídas al ver la lista
- Se guardan durante el curso académico
- Casos: empresa asignada, estado del acuerdo cambiado, proyecto calificado, etc.

### RF15 — Panel de Administración
|- Total de alumnos/profesores/empresas (activos/inactivos)
|- Nº de prácticas en curso / pendientes / finalizadas
|- Convenios firmados vs. no firmados (filtrable por alumno, grupo, empresa)
|- Nº de proyectos destacados por ciclo
|- Actividad reciente
|- Desactivar/reactivar usuarios
|- Gestión de estructura académica (familias, ciclos, líneas, grupos, asignaturas)

### RF16 — Historial de Cambios (Solo Admin)
- Registro de TODAS las modificaciones con antes/después
- Incluye: asignaciones, cambios de estado, anotaciones, proyectos, convenios, desactivaciones
- Un solo historial para todo
- Incluye quién hizo el cambio y cuándo

### RF17 — Gestión de Estructura Académica (Solo Admin)
- CRUD de familias profesionales
- CRUD de ciclos (vinculados a familia, con grado y duración)
- CRUD de líneas (mañana/tarde por ciclo)
- CRUD de grupos (vinculados a línea, con tutor asignado)
- CRUD de asignaturas (vinculadas a ciclo)
- Asignar profesor como tutor de grupo
- Asignar profesores a grupos (equipos educativos)
- Vista centralizada de toda la estructura académica

### RF18 — Promoción Anual de Curso (cambio de curso académico)
- Cada 1 de agosto el sistema prepara la promoción al curso siguiente (1º → 2º).
- Comando `academico:promocion-anual`:
  - `--preview`: muestra el plan (qué alumnos y a qué grupos pasan) sin modificar nada.
  - Ejecución normal: crea/usa el curso académico destino, crea los grupos del curso siguiente si no existen, mueve a los alumnos y registra todo en el historial de cambios.
  - `--curso-destino` (obligatorio) y `--curso-origen` (opcional; por defecto el activo).
- No vuelve a promocionar a un alumno que ya tiene grupo en el curso destino (idempotente).
- Los alumnos que estaban en 2º no promocionan (finalizan el ciclo); los repetidores se gestionan manualmente.
- Histórico conservado: la pertenencia alumno→grupo queda versionada por curso académico
  (columna `curso_academico_id` en `alumno_grupo`), de modo que se puede consultar
  en qué grupo estaba cada alumno en cursos anteriores.
- Todos los listados admiten filtrar por curso académico para consultar cursos anteriores.
- **Control del curso actual (admin):** en `admin/estructura/cursos` el admin puede crear cursos
  académicos y marcar cuál es el «curso actual» (único activo), desactivando los demás. El curso
  activo es el que se usa por defecto en la aplicación (grupos actuales, detección del origen de
  la promoción, etc.). Los cursos anteriores quedan disponibles para consultar el histórico.

---

## Requisitos No Funcionales

- **RNF1 — Rendimiento:** Soportar 2000-3000 alumnos simultáneos
- **RNF2 — Seguridad:** Autenticación robusta, roles bien definidos, datos sensibles protegidos
- **RNF3 — Escalabilidad:** Arquitectura que permita crecer sin reestructuración
- **RNF4 — UX:** Interfaz clara y sencilla, responsive (móvil/tablet/desktop)
- **RNF5 — Disponibilidad:** 99% uptime en periodo académico
- **RNF6 — Backup:** Backup diario de base de datos
- **RNF7 — Accesibilidad:** Logo del instituto visible, diseño profesional

---

## Estructura de Datos

### Tablas Principales

```
users (id, name, email, password, roles [JSON], email_verified_at, is_active, consent_rgpd, consent_rgpd_at, created_at, updated_at)
alumnos (id, user_id, linkedin_url, telefono, domicilio, fecha_nacimiento, tutor_practicas_id, created_at, updated_at)
alumno_grupo (id, alumno_id, grupo_id, curso_academico_id, created_at, updated_at) -- muchos a muchos versionado por curso académico (permite consultar cursos anteriores)
alumno_ciclo_matricula (id, alumno_id, ciclo_id, curso_academico, matriculado_at, created_at, updated_at)
profesores (id, user_id, familia_id nullable FK->familias, especialidad, es_tutor, es_coordinador_dual, created_at, updated_at)
grupo_profesor (id, grupo_id, profesor_id, asignatura_id nullable, created_at, updated_at) -- muchos a muchos; cada fila = el profesor imparte una asignatura en un grupo (unique grupo_id+profesor_id+asignatura_id)
empresas (id, nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at)
tutores_laborales (id, empresa_id, nombre, email, telefono, created_at, updated_at)
convenios (id, empresa_id, alumno_id, grupo_id, tutor_laboral_id, tutor_docente_id, numero_horas, fecha_inicio, fecha_fin, estado [firmado, no_firmado], fecha_firma, created_at, updated_at)
familias (id, codigo, nombre, descripcion, is_active, created_at, updated_at)
ciclos (id, familia_id, codigo, nombre, grado, duracion_anos, is_active, created_at, updated_at)
lineas (id, ciclo_id, turno [manana, tarde], nombre, is_active, created_at, updated_at)
grupos (id, linea_id, numero, nombre, tutor_id, is_active, created_at, updated_at)
asignaturas (id, ciclo_id, codigo, nombre, horas_semanales, es_practicas, is_active, created_at, updated_at)
profesor_asignatura (id, profesor_id, asignatura_id, created_at, updated_at)
calificaciones (id, alumno_id, asignatura_id, evaluacion, nota, created_at, updated_at)
anotaciones (id, alumno_id, profesor_id, texto, created_at, updated_at)
sustituciones (id, profesor_titular_id, profesor_sustituto_id, asignatura_id, grupo_id, fecha_inicio, fecha_fin, is_active, created_at, updated_at)
ofertas_practicas (id, empresa_id, creador_id, especialidad_requerida, num_alumnos, descripcion, estado, created_at, updated_at)
grupo_oferta (id, grupo_id, oferta_practica_id, created_at, updated_at) -- muchos a muchos; una oferta se dirige a varios grupos
solicitudes_practicas (id, oferta_id, alumno_id, estado [pendiente, aceptado, rechazado, retirado], created_at, updated_at)
practicas (id, alumno_id, oferta_id, empresa_id, tutor_laboral_id, fecha_inicio, fecha_fin, horas_acumuladas, convenio_firmado, created_at, updated_at)
proyectos (id, alumno_id, ciclo_id, curso_academico_id, titulo, descripcion, enlace_repositorio, enlace_despliegue, calificacion, es_destacado, destacado_por_id, created_at, updated_at)
proyecto_imagenes (id, proyecto_id, url, created_at, updated_at)
notificaciones (id, user_id, tipo, mensaje, enlace, leida, created_at, updated_at)
historial_cambios (id, usuario_id, modelo, modelo_id, accion, datos_antiguos [JSON], datos_nuevos [JSON], created_at, updated_at)
portfolio_estadisticas (id, clave, valor, created_at, updated_at)
```

---

## Arquitectura del Proyecto

```
ieshlanz/
├── SPEC.md
├── AGENTS.md
├── scripts/
│   └── init.sh
├── features.json
├── progress/
│   ├── README.md
│   ├── current.json
│   └── history.md
├── agents/
│   ├── implementer.md
│   └── reviewer.md
└── src/ (proyecto Laravel)
    ├── app/
    │   ├── Models/
    │   ├── Http/Controllers/
    │   ├── Services/
    │   └── Notifications/
    ├── database/
    │   ├── migrations/
    │   └── seeders/
    ├── resources/views/
    ├── routes/
    │   ├── web.php
    │   └── api.php
    └── public/
```

---

## Flujo de Ejecución

1. Crear SPEC.md (este documento)
2. Crear SDD harness (AGENTS.md, features.json, progress/, agents/, scripts/init.sh)
3. Inicializar proyecto Laravel
4. Implementar features en orden de features.json
5. Cada feature: leer SPEC → implementar → validar → marcar done
6. Commit y push tras cada fase importante

---

## Notas de Implementación

- Usar Laravel 10 con PHP 8.1+
- Blade templates + Tailwind CSS para frontend
- Alpine.js para interactividad ligera
- Laravel Policies para control de acceso por rol
- Laravel Events/Listeners para historial de cambios (observadores de modelos)
- Laravel Notifications para email + in-app
- Soft deletes para usuarios desactivados (o campo `is_active`)
- JSON fields para datos_antiguos/datos_nuevos en historial
- Imágenes de proyectos: Laravel Storage con `public` disk
- Portfolio público: ruta sin middleware de autenticación
- Panel admin: ruta con middleware `admin`
- Validar que las calificaciones no sean accesibles por alumnos (policy estricta)
- El profesor de proyectos solo ve proyectos de SU grupo
- **Tema visual:** Todas las vistas usan fondo claro (`bg-slate-50`), tarjetas blancas (`bg-white`), texto oscuro (`text-slate-900`). No usar `bg-gray-800` ni `text-white` en vistas de formulario.
- **Alumno-Grupo:** Relación muchos a muchos a través de `alumno_grupo`. El modelo Alumno tiene `grupos()` (belongsToMany), no `grupo()`.
- **Profesor-Grupo:** Relación muchos a muchos a través de `grupo_profesor`. El modelo Profesor tiene `gruposImpartidos()` (belongsToMany) con pivot `asignatura_id`: cada fila indica la asignatura que el profesor imparte en cada grupo. El modelo Grupo tiene `profesores()` con el mismo pivot.
- **Profesor-Familia:** El modelo Profesor tiene `familia_id` (nullable FK -> familias) y relación `familia()`. Se edita/visualiza en el perfil del profesor (create/edit/show).
- **Módulo Estructura Académica (implementado):** Panel admin bajo prefijo `admin/estructura` con rutas `admin.estructura.*`. Controladores en `App\Http\Controllers\Admin\Estructura\`. Views en `resources/views/admin/estructura/`. Árbol de navegación: Familias -> Ciclos (nº de cursos) -> Líneas/Turnos (mañana/tarde) -> Grupos, y Asignaturas por ciclo. Acceso desde el botón "Estructura Académica" en el dashboard admin y enlace en el menú desplegable de Admin.
- **Convenios:** No tienen `ciclo_id` ni `curso_academico`. Se vinculan a `alumno_id`, `grupo_id`, `tutor_laboral_id`, `tutor_docente_id`.