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
- Familias (estándar Ministerio) → Ciclos (DAW, DAM, SMR, Informática Básica...) → Líneas (mañana/tarde) → Cursos (1º, 2º)
- Grados: Básica, Media, Superior, Especialización, Acreditación
- Un alumno puede estar en varios ciclos a la vez
- Todo organizado por curso académico (26/27, 27/28...)
- Turnos solo a nivel agrupación de alumnos

### RF3 — Alumnado
- Datos personales: nombre, email, teléfono, dirección...
- Enlace a LinkedIn
- Ciclo, línea, curso, grupo asignado
- Tutor del ciclo asignado
- Tutor de prácticas asignado
- Estado: activo/inactivo

### RF4 — Profesorado
- Datos personales: nombre, email @ieshlanz.es, teléfono...
- Equipos educativos (a qué clases da)
- Asignaturas que imparte
- Sustitutos: definir quién sustituye y periodo (abierto)
- Roles: profesor, coordinador dual, admin

### RF5 — Asignaturas y Grupos
- Asignaturas por ciclo
- Grupos de clase
- Profesor asignado a cada grupo/asignatura
- Horarios de clase

### RF6 — Calificaciones
- Por evaluación (1ª, 2ª, 3ª) y/o módulo
- Calificación numérica
- Los alumnos NO ven sus calificaciones
- Cada profesor solo ve calificaciones de sus grupos

### RF7 — Tutorías y Anotaciones
- Anotaciones por alumno (ej. "Juan es bueno en BD")
- Pueden crear: tutor del grupo + coordinador Dual
- Se mantienen entre cursos
- El tutor puede ver anotaciones de cualquier clase a la que dé
- Editables con registro en historial

### RF8 — Empresas
- Datos fiscales: nombre, CIF, dirección, teléfono, email
- Responsable: nombre + DNI
- Tutores laborales: uno o varios (fijos por empresa, siempre de la empresa)
- Convenio Séneca: estado "firmado" / "no firmado"
- Filtrable por familia, especialidad, curso

### RF9 — Ofertas de Prácticas
- Creadas por profesor o empresa
- Especifica: especialidad requerida, nº de alumnos necesarios
- El profesor dirige la oferta a alumnos concretos (filtra por especialidad)

### RF10 — Solicitud y Asignación de Prácticas
- Alumno ve la oferta → puede postularse
- Profesor/Coordinador acepta/rechaza
- Estado: Pendiente → Aceptado / Rechazado
- Alumno puede rechazar antes de que el convenio esté firmado
- Alumno aceptado ve: datos empresa, tutor laboral, fechas, etc.

### RF11 — Historial de Horas
- Mínimo 500h entre 1º y 2º DAW
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
- Total de alumnos/profesores/empresas (activos/inactivos)
- Nº de prácticas en curso / pendientes / finalizadas
- Convenios firmados vs. no firmados (filtrable por familia, especialidad, curso)
- Nº de proyectos destacados por ciclo
- Actividad reciente
- Desactivar/reactivar usuarios

### RF16 — Historial de Cambios (Solo Admin)
- Registro de TODAS las modificaciones con antes/después
- Incluye: asignaciones, cambios de estado, anotaciones, proyectos, convenios, desactivaciones
- Un solo historial para todo
- Incluye quién hizo el cambio y cuándo

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
users (id, name, email, password, role, email_verified_at, is_active, created_at, updated_at)
alumnos (id, user_id, dni, telefono, direccion, linkedin, tutor_practicas_id, is_active, created_at, updated_at)
alumno_grupo (id, alumno_id, grupo_id, created_at, updated_at)
profesores (id, user_id, dni, telefono, departamento, is_active, created_at, updated_at)
grupo_profesor (id, grupo_id, profesor_id, created_at, updated_at)
coordinadores (id, profesor_id, created_at, updated_at) -- relación 1:1
admin_users (id, user_id, created_at, updated_at) -- relación 1:1
empresas (id, nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, created_at, updated_at)
tutores_laborales (id, empresa_id, nombre, email, telefono, created_at, updated_at)
convenios (id, empresa_id, ciclo_id, curso_academico, estado [firmado, no_firmado], fecha_firma, created_at, updated_at)
familias (id, nombre, created_at, updated_at)
ciclos (id, familia_id, nombre, grado, created_at, updated_at)
lineas (id, ciclo_id, nombre [mañana, tarde], created_at, updated_at)
cursos_academicos (id, nombre [26/27, 27/28...], fecha_inicio, fecha_fin, is_active, created_at, updated_at)
grupos (id, ciclo_id, linea_id, curso_academico_id, tutor_id, created_at, updated_at)
asignaturas (id, ciclo_id, nombre, created_at, updated_at)
grupo_asignatura (id, grupo_id, asignatura_id, profesor_id, created_at, updated_at)
calificaciones (id, grupo_asignatura_id, alumno_id, evaluacion, nota, created_at, updated_at)
anotaciones (id, alumno_id, profesor_id, texto, created_at, updated_at)
sustitutos (id, profesor_id, sustituto_id, fecha_inicio, fecha_fin, created_at, updated_at)
horarios (id, grupo_asignatura_id, dia, hora_inicio, hora_fin, aula, created_at, updated_at)
ofertas_practicas (id, empresa_id, creador_id [profesor/empresa], especialidad_requerida, num_alumnos, descripcion, estado, created_at, updated_at)
solicitudes_practicas (id, oferta_id, alumno_id, estado [pendiente, aceptado, rechazado, retirado], created_at, updated_at)
practicas (id, alumno_id, oferta_id, empresa_id, tutor_laboral_id, fecha_inicio, fecha_fin, horas_acumuladas, convenio_firmado, created_at, updated_at)
proyectos (id, alumno_id, ciclo_id, curso_academico_id, titulo, descripcion, enlace_repositorio, enlace_despliegue, calificacion, es_destacado, destacado_por_id, created_at, updated_at)
proyecto_imagenes (id, proyecto_id, url, created_at, updated_at)
notificaciones (id, user_id, tipo, mensaje, enlace, leida, created_at, updated_at)
historial_cambios (id, usuario_id, modelo, modelo_id, accion, datos_antiguos [JSON], datos_nuevos [JSON], created_at, updated_at)
portfolio_estadisticas (id, clave, valor, created_at, updated_at) -- calculadas, no almacenadas
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