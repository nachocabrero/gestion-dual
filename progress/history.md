## 2026-10-23 — Exclusión de alumnos repitiendo 1º en ofertas/prácticas/observaciones y ordenación insensible a tildes

- **Exclusión de alumnos que repiten 1º (`repitiendo_modulos_primero = true`)**:
  - `App\Models\Alumno`: nuevo scope `scopePuedenHacerPracticas($query)` que filtra alumnos donde `repitiendo_modulos_primero` es falso o nulo, en coherencia con el método `puedeHacerPracticas(): bool`.
  - **Asignación a ofertas de prácticas (`OfertaPracticaController.php`)**:
    - En `enviarForm`: los alumnos del grupo asignado a la oferta se filtran con `$a->puedeHacerPracticas()`, excluyendo a quienes repiten 1º.
    - En `enviarAAlumnos`: la verificación de alumnos de los grupos asignados utiliza `Alumno::puedenHacerPracticas()`.
  - **Alumnos del grupo sin práctica asignada (`GrupoPracticaController.php`)**:
    - En `getAlumnosSinPractica`: se descartan los alumnos que no pueden hacer prácticas (`!$a->puedeHacerPracticas()`).
  - **Creación de prácticas (`PracticaController.php`)**:
    - En `create`: tanto para docentes como para coordinadores y administradores, el selector de alumnos utiliza `Alumno::puedenHacerPracticas()`.
  - **Observaciones y anotaciones de clase (`AnotacionController.php`)**:
    - En `create`: la carga de alumnos del grupo seleccionado filtra exclusivamente alumnos con `puedenHacerPracticas()`, omitiendo a los alumnos repetidores de 1º.

- **Ordenación alfabética insensible a tildes/acentos (Á = A, É = E, Í = I, Ó = O, Ú/Ü = U)**:
  - Creado helper `App\Support\StrSort`:
    - `normalize(?string $str)`: translitera vocales mayúsculas y minúsculas con acento, diéresis o tilde (Á, É, Í, Ó, Ú, Ü, À, È, etc.) a sus vocales base en minúsculas.
    - `compare(?string $a, ?string $b)`: compara alfabéticamente dos cadenas con insensibilidad a tildes y mayúsculas/minúsculas.
    - `applyOrderBy($query, $column, $direction)`: aplica ordenación en base de datos; en SQLite registra y ejecuta `STR_SORT_KEY` garantizando que los tests y entornos SQLite ordenen "Ángel" antes que "Beatriz", y en MySQL aprovecha la colación nativa `utf8mb4_unicode_ci`.
  - `AppServiceProvider::boot`: registro automático de la función `STR_SORT_KEY` en eventos de conexión SQLite.
  - Aplicada ordenación insensible a tildes en:
    - Listado de empresas (`EmpresaController@index`, selector de profesor asignado en `create` y `edit`).
    - Listado de profesores (`ProfesorController@index`, empresas del profesor en `show`).
    - Listado de alumnos (`AlumnoController@index`, grupos en filtros).
    - Creación de anotaciones (`AnotacionController@create` y componente reactivo Alpine.js en `anotaciones/create.blade.php` con `localeCompare(..., 'es', { sensitivity: 'base' })`).
    - Ofertas de prácticas (`OfertaPracticaController@create`, `edit`, `enviarForm`).
    - Prácticas por grupos (`GrupoPracticaController@getAlumnosSinPractica` y `getAlumnosConPractica`).
    - Mensajería (`MensajeController@create`: profesores con empresas, empresas sin profesor y alumnos por grupo).
    - Administración de grupos (`GrupoController@show`: miembros y candidatos).
    - Formularios de registro (`RegisteredUserController` y `TeacherRegistrationController`).

- **Tests Automatizados**:
  - Creada suite `tests/Feature/ExclusionAlumnosRepiteYOdenacionTildesTest.php` con 11 tests específicos que verifican la normalización, comparación, exclusión en ofertas, grupos, creación de prácticas y anotaciones, así como la ordenación alfabética en empresas, profesores y alumnos (100% pasando).

## 2026-10-23 — Anotaciones: acceso a profesor solo para sus grupos y vista tabla por clase con ordenación dinámica por puesto

- **Control de acceso y filtro estricto por grupos del profesor (`AuthServiceProvider.php` y `AnotacionController.php`)**:
  - `Gate::define('create-anotacion')`: ampliado el permiso a usuarios con rol `profesor` (`User::ROLE_PROFESOR`), permitiendo al profesorado registrar anotaciones y clasificaciones de sus alumnos.
  - `AnotacionController::getGruposPermitidos`: si el usuario es docente, se filtran de forma estricta únicamente los grupos a los que imparte clase (`gruposImpartidos`) o de los que es tutor (`gruposTutor`). Los administradores y coordinadores duales conservan acceso a todos los grupos del centro.
  - En `create` y `store`, se valida de forma segura que un profesor no pueda visualizar ni registrar anotaciones sobre grupos o alumnos a los que no dé clase (respuesta 403 Forbidden ante cualquier acceso no autorizado).
- **Nueva interfaz de creación estructurada como tabla de clase (`resources/views/anotaciones/create.blade.php`)**:
  - Selector desplegable de grupo/clase que permite alternar con fluidez entre las clases a las que el docente tiene acceso.
  - Tabla completa del grupo seleccionado donde cada fila corresponde a un alumno de la clase, mostrando la foto del alumno al lado de su nombre en lugar de la inicial (con icono neutral si aún no ha subido foto), correo e identificador de puesto.
  - Inputs compactos y optimizados para:
    - **Puesto en la clase**: valor numérico (1 = mejor puesto).
    - **Título**: encabezado breve de la observación (ej. "Rendimiento en BD", "Excelente actitud", etc.).
    - **Contenido**: campo de texto compacto para la anotación u observación (no voluminoso, ideal para revisiones rápidas).
- **Reordenación dinámica en tiempo real por puesto de clase (Alpine.js)**:
  - Componente reactivo `anotacionesGrupoApp` que ordena automáticamente la lista de alumnos en el navegador conforme el profesor escribe o modifica el puesto numérico (`sortedAlumnos`).
  - Los alumnos con puesto asignado se posicionan en la cabecera en riguroso orden numérico (1, 2, 3...) y los alumnos sin puesto permanecen agrupados a continuación.
  - Sincronización con `updatePuesto` y `debounce` para una experiencia de escritura fluida sin perder el foco ni saltos abruptos.
- **Guardado en lote y compatibilidad individual (`AnotacionController@store`)**:
  - Soporte de guardado masivo de las anotaciones introducidas en la tabla del grupo con un único botón de acción ("Guardar Anotaciones del Grupo").
  - Si el profesor ya contaba con una anotación previa para ese alumno, se actualiza automáticamente su puesto y contenido evitando duplicidades.
  - Mantenimiento íntegro de la compatibilidad con el guardado individual.
  - Actualización del botón "Nueva Observación / Puestos" en `anotaciones.index` condicionado con `@can('create-anotacion')`.
- **Tests Automatizados**:
  - Creada suite `tests/Feature/AnotacionTablaGrupoProfesorTest.php` con 6 tests específicos (28 aserciones).
  - Actualizados los 30 tests de `tests/Feature/AnotacionModuleTest.php` (60 aserciones), pasando los 36 tests al 100%.

## 2026-10-23 — Empresas activas por defecto, iconos de estado, supresión columna Estado y actualización académica DAM y DAW

- **Listado de empresas con filtro por defecto de empresas activas (`EmpresaController.php` y `empresas/index.blade.php`)**:
  - `EmpresaController@index`: el parámetro `active` toma por defecto `'1'`, filtrando únicamente empresas activas (`is_active = true`). Admite `'0'` para inactivas y `'todas'` para ver el catálogo completo.
  - `resources/views/empresas/index.blade.php`: selector de filtro actualizado con "Solo Activas (por defecto)", "Solo Inactivas" y "Todas las empresas". Botón "Limpiar" visible cuando se aplica cualquier filtro no por defecto.
- **Sustitución del avatar con inicial por icono de estado activo/inactivo**:
  - Eliminado el recuadro con la inicial del nombre de la empresa tanto en la tabla de escritorio como en las tarjetas móviles.
  - Sustituido por un icono intuitivo con badge semántico: check verde en contenedor esmeralda para empresas activas (`title="Empresa activa"`), e icono de bloqueo/cancelación en contenedor rojizo para empresas inactivas (`title="Empresa inactiva"`).
- **Supresión de la columna redundante ESTADO**:
  - Retirada la columna `<th>Estado</th>` y su correspondiente celda `<td>` de la tabla de escritorio en `empresas.index`, ya que el estado se identifica de inmediato mediante el icono junto al nombre.
  - Ajustado el `colspan` del estado vacío a 8 columnas.
- **Exclusión de empresas inactivas en el envío de mensajes (`MensajeController.php`)**:
  - En `create()`, los datos JSON interactivos para Alpine.js (`profesoresEmpresasData` y `empresasSinProfesorData`) cargan exclusivamente empresas activas.
  - En `store()`, cualquier empresa inactiva queda descartada de la resolución de destinatarios, impidiendo que reciba comunicados o que se creen usuarios destinatarios para empresas desactivadas.
- **Actualización de asignaturas de la estructura académica (1º DAM, 1º DAW y 2º DAM)**:
  - Migración `database/migrations/2026_10_23_121500_update_asignaturas_dam_and_daw.php` y actualización en `database/seeders/AcademicStructureSeeder.php`.
  - 1º DAM y 1º DAW:
    - Programación (PROG)
    - Bases de Datos (BD)
    - Sistemas Informáticos (SI)
    - Lenguajes de Marcas y Sistemas de Gestión de Información (LMSGI)
    - Entornos de Desarrollo (ED)
    - Itinerario Personal para la Empleabilidad I (IPE I)
    - Digitalización Aplicada a los Sectores Productivos (DASP/DIG)
    - Sostenibilidad Aplicada al Sistema Productivo (SASP/SOST)
  - 2º DAM:
    - Acceso a Datos (AD)
    - Desarrollo de Interfaces (DI)
    - Programación Multimedia y Dispositivos Móviles (PMDM)
    - Programación de Servicios y Procesos (PSP)
    - Sistemas de Gestión Empresarial (SGE)
    - Inglés Profesional (o módulo técnico equivalente de idiomas aplicado al entorno laboral) (INGLES/ING)
    - Itinerario Personal para la Empleabilidad II (IPE II)
    - Proyecto Intermodular (PIM/PI)
  - Desasignación segura de docentes y eliminación limpia de asignaturas obsoletas previas de DAM en `profesor_asignatura`, `grupo_profesor`, `sustituciones` y `calificaciones`.
- **Tests Automatizados**:
  - `tests/Feature/EmpresasActivasAsignaturasDamDawTest.php`: 6 tests y 53 aserciones pasando al 100%.

## 2026-10-23 — Perfil de profesor: empresas de seguimiento y prácticas actuales con desplegable de cursos anteriores

- **Visualización de empresas de seguimiento en perfil del profesor (`resources/views/profesores/show.blade.php`)**:
  - `app/Http/Controllers/ProfesorController.php`: en `show()`, carga las empresas de seguimiento que tiene asignadas el docente (`$profesor->empresas()->orderBy('nombre')->get()`).
  - Tarjeta con diseño Stitch "Empresas en Seguimiento" mostrando contador de empresas, nombre con enlace a la ficha, CIF, estado activo/inactivo, datos de contacto (email, teléfono, dirección) y botón de acceso rápido.
  - Estado vacío informativo cuando el profesor no tiene empresas asignadas.
- **Prácticas tutorizadas del curso actual y cálculo del tutor docente**:
  - `app/Models/Alumno.php`: añadida relación `convenios(): HasMany` (`hasMany(Convenio::class)`).
  - En `ProfesorController@show`, se obtienen las prácticas donde el docente es tutor docente:
    - Vía alumno (`$alumno->tutor_practicas_id === $profesor->user_id`).
    - Vía convenio (`$convenio->tutor_docente_id === $profesor->id`).
  - Se identifican las prácticas del curso académico actual (`curso_academico_id === $cursoActual->id` o sin curso asignado).
  - Tarjeta "Prácticas Tutorizadas — Curso Actual" con tabla detallada: alumno (avatar con inicial y nombre), empresa colaboradora con enlace, tutor laboral, periodo de fechas, horas acumuladas, estado del convenio (badge Firmado/Pendiente) y enlace al detalle de la práctica.
- **Desplegable interactivo para historial de prácticas de cursos anteriores**:
  - `ProfesorController.php`: agrupa las prácticas tutorizadas de cursos anteriores por curso académico (`$cursosAnterioresConPracticas`).
  - En la vista `profesores.show`, el bloque de cursos anteriores **solo se muestra si el docente fue tutor docente en algún curso anterior**. Si no tiene historial de cursos previos, el bloque se omite automáticamente.
  - Componente reactivo con Alpine.js que incluye un desplegable (`<select id="select-curso-anterior">`) para alternar entre los cursos académicos anteriores y consultar sus prácticas tutorizadas.
- **Mejoras de navegación y control de acceso**:
  - `resources/views/profesores/show.blade.php`: botón "Editar" condicionado con `@can('update-profesor', $profesor)` para evitar enlaces a 403 en docentes estándar. Botón "Volver" adaptado al rol del usuario (listado de profesores o dashboard).
  - `resources/views/layouts/app.blade.php`: añadido enlace "Mi Perfil Docente" en el desplegable de usuario para profesores (`auth()->user()->profesor`).
- **Tests automatizados**:
  - Creada suite `tests/Feature/ProfesorPerfilEmpresasPracticasTest.php` con 6 tests completos verificando empresas en seguimiento, prácticas del curso actual, desplegable condicional de cursos anteriores y vinculación de tutor docente por convenio. 18 tests de profesores pasando al 100%.

## 2026-10-23 — Asignación de seguimiento de empresas a profesores, visualización en listados y mensajería selectiva a empresas

- **Seguimiento de empresas por profesor (Relación 1:N)**:
  - Migración `database/migrations/2026_10_23_110000_add_profesor_id_to_empresas_table.php`: añadida columna `profesor_id` (foreign key nullable con `nullOnDelete()`) a la tabla `empresas`.
  - `app/Models/Empresa.php`: añadido `profesor_id` a `$fillable` y `$casts`, relación `profesor()` (`belongsTo(Profesor::class)`), y scopes `scopeByProfesor()` y `scopeSinProfesor()`.
  - `app/Models/Profesor.php`: añadida relación `empresas()` (`hasMany(Empresa::class)`), permitiendo a cada profesor gestionar el seguimiento de múltiples empresas.
  - `app/Http/Controllers/EmpresaController.php`:
    - `index()`: eager loading de `profesor.user`, soporte para filtro por profesor (`profesor_id`) y por empresas no asignadas (`profesor_id=sin_asignar`), pasando la lista de profesores para el filtro en la toolbar.
    - `create()` y `edit()`: cargan y pasan la colección de profesores activos para seleccionarlos en el desplegable de seguimiento.
    - `store()` y `update()`: validación de `'profesor_id' => ['nullable', 'exists:profesores,id']` y guardado seguro del profesor de seguimiento.
    - `show()`: eager loading de `profesor.user` para mostrar los datos del profesor asignado.
  - Vistas de empresas:
    - `resources/views/empresas/index.blade.php`: nueva columna "Seguimiento" en la tabla de escritorio con badge informativo (nombre del profesor o "Sin profesor asignado"), visualización en la tarjeta móvil, y filtro dropdown en la barra de herramientas.
    - `resources/views/empresas/create.blade.php` y `resources/views/empresas/edit.blade.php`: selector "Profesor de Seguimiento" con opción "Sin profesor asignado (opcional)".
    - `resources/views/empresas/show.blade.php`: bloque "Profesor de Seguimiento" con nombre del docente y correo electrónico.
- **Mensajería a empresas con filtros combinados (profesores y sin profesor)**:
  - `app/Http/Controllers/MensajeController.php`:
    - `create()`: carga profesores con sus empresas activas asignadas (`$profesoresConEmpresas`) y empresas activas sin profesor (`$empresasSinProfesor`), preparando estructuras JSON para Alpine.js.
    - `store()`: acepta `tipo_destinatario = 'empresas'`, validando combinaciones de `profesor_ids`, `sin_profesor` y/o `empresa_ids`. Resuelve las empresas destinatarias, crea o localiza cuentas de usuario con rol `ROLE_EMPRESA` a partir de sus correos de contacto, y emite las notificaciones del sistema y correos electrónicos con `NotificacionService`.
  - Vistas de mensajería:
    - `resources/views/mensajes/create.blade.php`: añadida la 4ª tarjeta de audiencia "Empresas colaboradoras". Panel reactivo con Alpine.js que permite seleccionar o deseleccionar docentes (marcando automáticamente sus empresas asociadas), el bloque de "Empresas sin profesor asignado", o afinar individualmente por empresa. Contador en tiempo real de empresas seleccionadas.
    - `resources/views/mensajes/index.blade.php` y `resources/views/mensajes/show.blade.php`: soporte visual completo (icono y badge color ámbar para la audiencia "Empresas").
- **Tests automatizados**:
  - `tests/Feature/EmpresaSeguimientoProfesorTest.php`: 7 tests completos que validan la creación y edición con profesor, listado con filtro, carga de formulario de mensajería y emisión combinada a empresas por profesor y sin profesor. Todos pasando al 100% junto a los 25 tests de `EmpresaModuleTest` y 9 tests de `MensajeriaTest`.

## 2026-10-22 — Audiencia única en mensajes, auto-marcado de notificaciones al ver detalle y 4 estados de ofertas de prácticas

- **Audiencia única en detalle del mensaje (`resources/views/mensajes/show.blade.php`)**:
  - Eliminado el párrafo duplicado de audiencia en la cabecera del mensaje (`<p class="..."><x-destinatarios-mensaje ... /></p>`), manteniéndose únicamente dentro de la tarjeta del mensaje (`Audiencia: <strong><x-destinatarios-mensaje ... /></strong>`).
- **Marcado automático de notificación como leída al ver detalles**:
  - `routes/web.php`: añadida ruta `GET /notificaciones/{notificacion}/abrir` con nombre `notificaciones.abrir`.
  - `app/Http/Controllers/NotificacionController.php`: implementado el método `abrir()` que autoriza que la notificación pertenezca al usuario autenticado, la marca como leída (`es_leida = true`) y, si corresponde a un mensaje recibido, actualiza también el timestamp `leido_at` en la tabla pivote `mensaje_destinatarios`. Posteriormente redirige a la URL de destino de la notificación (o a `notificaciones.index` si no tiene enlace).
  - `resources/views/notificaciones/index.blade.php` y `resources/views/dashboard.blade.php`: los botones y enlaces de "Ver detalle" ahora invocan `route('notificaciones.abrir', $notif)`.
- **Nuevo sistema de 4 estados para Ofertas de Prácticas (`pendiente`, `abierta`, `solicitada`, `cerrada`)**:
  - Migración `database/migrations/2026_10_22_092500_update_ofertas_practicas_estados.php`: ampliada la columna `estado` en la tabla `ofertas_practicas` a `VARCHAR(30)` y convertidos los registros previos de `'activa'` a `'abierta'`.
  - `app/Models/OfertaPractica.php`: constantes de estado `ESTADO_PENDIENTE`, `ESTADO_ABIERTA`, `ESTADO_SOLICITADA`, `ESTADO_CERRADA`, métodos de consulta `esPendiente()`, `esAbierta()`, `esSolicitada()`, `esCerrada()`, y scopes `scopeAbiertas()` y `scopeActivas()` (con soporte retrocompatible para `'activa'`).
  - `app/Http/Controllers/OfertaPracticaController.php`:
    - `index()`:
      - Estado `pendiente`: visible únicamente para profesores, excluido para alumnos.
      - Estado `abierta` (o `activa`): visible para profesores y todos los alumnos cuyos grupos estén asignados a la oferta.
      - Estado `solicitada` y `cerrada`: visible para profesores y exclusivamente para aquellos alumnos que han sido asignados por el docente a dicha oferta.
    - `show()`: políticas de acceso coherentes por estado; rechaza con `403` a alumnos que intenten acceder a ofertas `pendiente`, o a ofertas `solicitada`/`cerrada` si no están asignados a ellas.
    - `postularse()`: permite postulación de alumnos solo si el estado es `abierta` y el alumno pertenece a uno de los grupos destinatarios.
    - `enviarAAlumnos()`: al asignar y enviar la oferta a alumnos seleccionados por el profesor, actualiza su estado automáticamente a `'solicitada'`.
  - `app/Http/Controllers/PracticaController.php`:
    - En el método `store()`, al crearse una práctica vinculada a una oferta, si el número de prácticas alcanza el total de plazas ofertadas (`$oferta->practicas()->count() >= $oferta->num_alumnos`), la oferta se actualiza automáticamente a estado `'cerrada'`.
  - `app/Http/Controllers/GrupoPracticaController.php`:
    - En `getOfertasConPlazasLibres()` se incluyen las ofertas en estado `pendiente`, `abierta`, `activa` y `solicitada`, reflejándose así en la gestión de prácticas y prácticas por grupos para los profesores.
  - Vistas actualizadas (`resources/views/ofertas/index.blade.php`, `resources/views/ofertas/show.blade.php`, `resources/views/ofertas/create.blade.php`, `resources/views/ofertas/edit.blade.php`, `resources/views/empresas/show.blade.php`):
    - Badges y filtros actualizados para los 4 estados con colores distintivos (Pendiente: ámbar, Abierta: esmeralda, Solicitada: azul/índigo, Cerrada: pizarra/gris).
    - En `show.blade.php`, los alumnos de grupos asignados pueden pulsar en "Solicitar esta plaza" cuando la oferta está abierta; una vez solicitada se indica "Solicitud registrada".
- **Tests Automatizados**:
  - Creada suite `tests/Feature/OfertaEstadosAndNotificacionesFixTest.php` con 6 tests completos verificando los 3 ítems solicitados, ejecutados y pasando al 100%.

## 2026-10-22 — Edición de domicilio y fecha de nacimiento en perfil de alumno y truncado de destinatarios de mensajes

- **Edición de domicilio y fecha de nacimiento en perfil de alumno**:
  - `app/Http/Requests/ProfileUpdateRequest.php`:
    - Incorporadas las reglas de validación opcionales para `domicilio` (`['nullable', 'string', 'max:500']`) y `fecha_nacimiento` (`['nullable', 'date']`).
  - `app/Http/Controllers/ProfileController.php`:
    - Actualizado el método `update` para que, cuando el usuario autenticado tenga perfil de alumno (`$user->alumno`), se persistan o actualicen `domicilio` y `fecha_nacimiento` de forma limpia (permitiendo valores opcionales o vacíos en caso de ser deseleccionados).
  - `resources/views/profile/partials/update-profile-information-form.blade.php`:
    - Añadidos los campos editables de fecha de nacimiento (`type="date"`) y domicilio (`type="text"`) con diseño Stitch UI y soporte dark mode, indicando explícitamente su carácter opcional.
- **Truncado de destinatarios de mensajes largos (>100 caracteres) y despliegue interactivo**:
  - `resources/views/components/destinatarios-mensaje.blade.php`:
    - Creado componente Blade reutilizable que evalúa la longitud del texto de destinatarios.
    - Si supera 100 caracteres, trunca a 100 caracteres añadiendo `"..."` (`mb_substr($texto, 0, 100) . '...'`).
    - Integra interactividad con Alpine.js (`@mouseenter="hovered = true"`, `@mouseleave="hovered = false"`, `@click.stop="expanded = !expanded"`), mostrando la lista completa tanto al pasar el ratón como al pinchar encima.
    - Incluye atributo `title` accesible con el texto completo y estilizado con subrayado punteado sutil que indica interactividad.
    - Si la lista tiene 100 caracteres o menos, se muestra de forma íntegra sin puntos suspensivos.
  - `resources/views/mensajes/show.blade.php` y `resources/views/mensajes/index.blade.php`:
    - Actualizados para usar `<x-destinatarios-mensaje :destinatarios="..." />` en cabecera, insignia de audiencia y columna de tabla.
- **Tests Automatizados**:
  - Creadas suites en `tests/Feature/ProfileAlumnoDomicilioFechaTest.php` (3 tests) y `tests/Feature/MensajeDestinatariosTruncarTest.php` (2 tests), pasando al 100%.

## 2026-10-21 — fix: Contraste y legibilidad de texto y etiquetas en formulario de alumno con fondo oscuro

- **Problema corregido**:
  - El texto destacado "repitiendo con módulos pendientes de 1º" se renderizaba en `text-amber-900` (marrón oscuro `#78350f`), resultando ilegible sobre fondos oscuros.
  - Las etiquetas de los campos "Empresa de prácticas (1º curso)" y "Horas de prácticas en 1º" en el componente `x-input-label` tenían únicamente `text-slate-700` (`#334155`), careciendo de variante clara para modo oscuro.
- **Solución implementada**:
  - `resources/views/alumnos/edit.blade.php`, `resources/views/profile/partials/update-profile-information-form.blade.php` y `resources/views/alumnos/create.blade.php`:
    - Sustituido el marrón oscuro por `font-bold text-amber-500 dark:text-amber-300`, garantizando alta luminosidad y contraste sobre cualquier fondo sin tonalidades marrones.
    - Asignadas clases explícitas de contraste `font-bold text-slate-800 dark:text-slate-100` a las etiquetas de empresa y horas de 1º curso.
    - Badges de no elegibilidad adaptados con `dark:bg-amber-900/60 dark:text-amber-200 dark:border-amber-700`.
  - `resources/views/components/input-label.blade.php`: añadido `dark:text-slate-200` para garantizar legibilidad global en modo oscuro.
  - `resources/views/layouts/app.blade.php`: incorporada detección temprana de preferencia de modo oscuro del sistema/navegador para activación instantánea de `.dark`.
  - Recompilados assets de producción con `npm run build` y superada la suite de tests (`9 tests, 59 assertions OK`).

## 2026-10-21 — Checks de prácticas y repetición en perfil de alumno

- **Migración y estructura de datos (`2026_10_21_140000_add_practicas_checks_to_alumnos_table.php`)**:
  - Columnas booleanas añadidas a `alumnos`:
    - `sin_practicas_primero` (default false): no ha realizado ninguna hora de prácticas en este ciclo anteriormente (convalidación de 1º, etc.).
    - `repitiendo_modulos_primero` (default false): repitiendo con módulos pendientes de 1º (no realiza prácticas este año según normativa).
- **Modelo Alumno (`app/Models/Alumno.php`)**:
  - Actualizados `$fillable` y `$casts` para ambos campos booleanos.
  - Métodos helper añadidos:
    - `puedeHacerPracticas(): bool`: devuelve `false` si el alumno está repitiendo módulos de 1º.
    - `haRealizadoPracticasPrimero(): bool`: calcula si el alumno tiene prácticas y horas reales de primer año.
- **Formulario de Perfil (`resources/views/profile/partials/update-profile-information-form.blade.php`)**:
  - Anotación explicativa destacada:
    - Información para alumnos en 2º repitiendo (pueden registrar prácticas de 1º aunque las hicieran hace 2 o más años).
    - Información sobre repetición con módulos de 1º (bloqueo de prácticas este año).
    - Información para convalidaciones de 1º curso (registro formal de 0 horas anteriores).
  - Check 1: *«No he realizado ninguna hora de prácticas en este ciclo formativo anteriormente»*.
  - Check 2: *«Estoy repitiendo con módulos de 1º curso (este año no realizo prácticas)»*.
  - Reactividad con Alpine.js para deshabilitar / ocultar campos de empresa y horas de 1º si marca sin prácticas previas.
- **Controladores y validación (`ProfileUpdateRequest`, `ProfileController`, `AlumnoController`, `RegisteredUserController`)**:
  - Validación booleana nullable de ambos checks.
  - Si marca `sin_practicas_primero`, se normaliza a 0 horas y empresa `null`.
  - Guardado completo tanto en perfil propio del alumno (`profile.update`) como en administración/edición (`alumnos.update` y `alumnos.store`).
- **Visualización en Vistas Institucionales**:
  - `alumnos/edit.blade.php`: panel de edición para profesorado/admin con los dos checks y la anotación informativa.
  - `alumnos/create.blade.php`: formulario de alta con los checks y notas de repetición.
  - `alumnos/show.blade.php`: visualización de badges informativos *«Repitiendo módulos de 1º (Sin prácticas)»* y *«0h anteriores (Convalidación 1º)»*, con alerta sobre no elegibilidad para prácticas.
  - `alumnos/index.blade.php`: badges en tabla de escritorio y tarjetas móviles para *«Sin prácticas 1º (0h)»* y *«No hace prácticas (Repite 1º)»*.
  - `practicas/grupos/show.blade.php`: indicador visual al lado del alumno y estado de 1º curso.
- **Tests Automatizados**:
  - `tests/Feature/AlumnoPracticasChecksTest.php`: 4 pruebas (31 aserciones) pasando al 100%.
  - `tests/Feature/AlumnoPracticasPrimeroTest.php`: 5 pruebas (28 aserciones) pasando al 100%.

## 2026-10-21 — Configuración de SMTP Google Workspace, mensajería y recuperación de contraseña

- **Configuración SMTP Google Workspace en `.env`**:
  - `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_ENCRYPTION=tls`.
  - Credenciales protegidas en `.env` bajo `MAIL_USERNAME=informatica_dual@ieshlanz.es` y `MAIL_PASSWORD=isaiascoordinador`.
  - Remitente oficial: `informatica_dual@ieshlanz.es` con nombre `Gestión DUAL H.Lanz`.
- **Reanudación del despacho de notificaciones y mensajes por SMTP (`MensajeController.php`)**:
  - Conectado el envío de correo institucional para cada destinatario activo mediante `NotificacionService::enviar(...)`, vinculando el aviso in-app y el envío por correo electrónico con plantilla oficial.
- **Personalización institucional de recuperación de contraseña**:
  - `app/Providers/AuthServiceProvider.php`:
    - Registrado `ResetPassword::toMailUsing(...)` para generar notificaciones de restablecimiento de contraseña corporativas.
  - `resources/views/emails/reset-password.blade.php`:
    - Creada plantilla corporativa de recuperación de contraseña con logotipo del centro, botón de restablecimiento de acceso seguro con caducidad de 60 minutos, enlace de respaldo y pie de protección de datos RGPD.
- **Traducción y localización al español del flujo de recuperación de contraseña**:
  - `resources/views/auth/forgot-password.blade.php`: completamente traducida y adaptada al diseño corporativo en español ("¿Olvidaste tu contraseña?", texto descriptivo, "Correo electrónico", botón "Enviar enlace", botón "Volver a Iniciar sesión").
  - `resources/views/auth/reset-password.blade.php`: traducida al español ("Restablecer contraseña", "Nueva contraseña", "Confirmar nueva contraseña", "Guardar nueva contraseña").
  - Publicación y configuración de archivos de idioma (`lang/es/passwords.php`, `lang/es/auth.php`) y ajuste de `config/app.php` a `'locale' => 'es'`.
- **Resolución del error de envío (mailpit a smtp.gmail.com)**:
  - Causa raíz identificada: el contenedor Docker `ieshlanz-app` mantenía en la memoria de su proceso PHP-FPM las variables de entorno antiguas (`MAIL_HOST=mailpit`), ignorando los cambios de `.env`.
  - Se recargó el contenedor (`docker compose up -d app`), aplicando correctamente `MAIL_HOST=smtp.gmail.com` y `MAIL_PORT=587`.
- **Tests Automatizados**:
  - `tests/Feature/Auth/PasswordResetTest.php`: 5 pruebas (13 aserciones) pasando al 100%, verificando la generación del enlace, el restablecimiento efectivo y el uso de la plantilla institucional y asunto oficial.
  - `tests/Feature/MensajeriaTest.php`: 9 pruebas (49 aserciones) pasando al 100%.

## 2026-10-21 — Persistencia definitiva y supresión del aviso de cookies

- **Supresión inteligente del banner de cookies (`components/cookie-banner.blade.php`)**:
  - Comprobación en servidor antes de inyectar el componente en el DOM: si el usuario está autenticado y ya consintió cookies (`consent_cookies_at`), aceptó RGPD (`consent_rgpd_at` o `consent_rgpd`), o si la petición contiene la cookie o sesión de consentimiento, el banner ni siquiera se renderiza.
  - Almacenamiento redundante en cliente: `localStorage.setItem('cookie_consent', 'true')` y `document.cookie` no cifrada con 1 año de caducidad.
- **Soporte de cookies no encriptadas (`EncryptCookies.php`)**:
  - Se añadió `cookie_consent` a la lista `$except` de `EncryptCookies` para evitar que Laravel corrompa o descarte el valor establecido por JavaScript.
- **Sincronización en inicio de sesión (`AuthenticatedSessionController.php` y `CookieController.php`)**:
  - Al iniciar sesión en el sistema, si el usuario ya había aceptado cookies en el navegador o aceptó políticas RGPD, se sincroniza automáticamente `consent_cookies_at = now()` en su registro de usuario y en la sesión, garantizando que el aviso nunca vuelva a mostrarse.
- **Tests Automatizados**:
  - Añadido `tests/Feature/CookieConsentTest.php` con 5 tests específicos (19 aserciones) pasando al 100%.

## 2026-10-21 — Visibilidad del seguimiento de mensajes, check para marcar avisos leídos y campana verde

- **Seguimiento de entrega y lecturas exclusivo para Admin y Coordinador Dual**:
  - `app/Http/Controllers/MensajeController.php` y `resources/views/mensajes/show.blade.php`:
    - El bloque de métricas y auditoría de lecturas (trazabilidad por destinatario con marcas temporales) ahora solo se visualiza si el usuario conectado cuenta con rol de Administrador o Coordinador Dual (`$esAdminOCoordinador`).
    - Destinatarios regulares (alumnado y profesorado) ven exclusivamente el comunicado oficial sin acceso al panel de supervisión de lecturas ni a botones de gestión de mensajería.
- **Check para marcar avisos/notificaciones como leídos**:
  - `routes/web.php` y `app/Http/Controllers/NotificacionController.php`:
    - Añadidas rutas `POST /notificaciones/{notificacion}/marcar-leida` y `POST /notificaciones/marcar-todas`.
    - Eliminado el auto-marcado indiscriminado al entrar al listado de notificaciones para permitir gestión manual y precisa de avisos pendientes.
    - Sincronización bidireccional: al marcar como leída una notificación de mensaje oficial, se actualiza también en `mensaje_destinatarios`.
  - `resources/views/dashboard.blade.php`:
    - En el bloque "Últimos Avisos", cada aviso dispone de un botón interactivo con icono de check para marcarlo como leído individualmente.
  - `resources/views/notificaciones/index.blade.php`:
    - Incorporado botón con icono de check "Marcar leído" por cada notificación pendiente, y botón superior "Marcar todas como leídas".
- **Marca verde en la campana de notificaciones de la cabecera**:
  - `resources/views/layouts/app.blade.php`:
    - La campana de la cabecera muestra una marca verde (`bg-emerald-500 rounded-full ring-2 ring-white animate-pulse`) mientras existan avisos pendientes sin leer.
- **Tests Automatizados**:
  - Verificados en `MensajeriaTest` y `NotificacionModuleTest` (100% en verde).

## 2026-10-21 — Pausa de SMTP en mensajería y asignación de módulo/asignatura en registro de profesores

- **Pausa de SMTP y optimización de envío de mensajes directos**:
  - Resuelto error 500 al enviar comunicados:
    - La columna `resumen_destinatarios` se amplió a tipo `TEXT` para prevenir desbordamientos por listas largas de destinatarios.
    - El envío por servidor SMTP se ha pausado temporalmente según lo solicitado; los comunicados se entregan de forma directa e inmediata a través del sistema in-app de notificaciones (`NotificacionService::crear`), eliminando cualquier dependencia del servidor de correo no configurado.
    - Se garantiza retrocompatibilidad en tests unitarios.
- **Asignación obligatoria de módulo / asignatura por grupo en registro de profesores (`/teacher`)**:
  - `resources/views/auth/register-teacher.blade.php`:
    - Cada grupo seleccionado despliega reactivamente un menú de selección con los módulos y asignaturas correspondientes a su ciclo formativo (`$grupo->linea->ciclo->asignaturas`).
    - Al marcar el grupo, el campo del módulo/asignatura se activa con validación obligatoria.
  - `app/Http/Controllers/Auth/TeacherRegistrationController.php`:
    - En `create()`: precarga de asignaturas de ciclo asociadas a cada grupo.
    - En `store()`: validación personalizada para exigir el módulo/asignatura de cada grupo marcado.
    - Almacenamiento en la tabla pivote `grupo_profesor` con el campo `asignatura_id` y sincronización en `profesor_asignatura`.
  - `tests/Feature/TeacherRegistrationTest.php`:
    - Nuevos tests de persistencia de docencia y verificación de error de validación cuando falta la asignatura en un grupo marcado.
    - 100% de tests pasando (11 tests, 71 aserciones).

## 2026-10-21 — Mejoras en creación de prácticas, ofertas compartidas y acceso docente a empresas

- **Corrección en redacción de mensajes (`mensajes/crear`)**:
  - Trasladada la lógica reactiva de Alpine.js de atributos HTML inline a una función `mensajeriaForm()` en bloque `<script>` evitando que los caracteres de función flecha (`=>`) sean interpretados como cierres de etiquetas por el navegador.
- **Detalle de Oferta (`ofertas/show.blade.php`)**:
  - Corregido el color y contraste de la tipografía del título principal y cabeceras a `text-slate-900 font-bold font-display`.
  - El estado de la oferta de cada alumno candidato se muestra como `Pendiente` (badge ámbar) hasta que se registra su práctica con la empresa, momento en el cual pasa dinámicamente a `Asignado` (badge esmeralda).
  - Añadido botón de acción directo "Crear práctica" por cada alumno candidato pendiente, precargando automáticamente el alumno y la empresa de la oferta. Si ya existe la práctica, muestra enlace directo a "Ver práctica".
- **Ficha ampliada en Oferta Compartida con la Empresa (`ofertas/compartida.blade.php`)**:
  - Eliminada la columna "Estado en Oferta".
  - Ficha detallada para cada alumno candidato con avatar, teléfono (con enlace directo `tel:`), fecha de nacimiento formateada, y chips con enlace directo a GitHub, LinkedIn y Portfolio. Los campos no rellenados se ocultan limpiamente.
- **Acceso del profesorado al módulo de Empresas**:
  - Actualizado el middleware de rutas `empresas` en `routes/web.php` a `role:admin,coordinador_dual,profesor`.
  - Actualizadas las comprobaciones `abort_unless` en `EmpresaController` (`index`, `create`, `store`, `show`, `edit`, `update`, `storeTutorLaboral`, `updateTutorLaboral`, `destroyTutorLaboral`) para dar acceso pleno a profesores.
  - Habilitado el enlace de Empresas en la barra lateral de navegación para profesores.
  - Añadido formulario desplegable en `empresas/show.blade.php` para dar de alta tutores laborales directamente en la ficha de la empresa.
- **Mejoras en Creación de Prácticas (`practicas/create.blade.php` y `PracticaController`)**:
  - Soporte para parámetros de precarga `oferta_id`, `alumno_id` y `empresa_id`.
  - Selector dinámico de tutores laborales filtrado exclusivamente a los tutores de la empresa seleccionada con reseteo automático al cambiar de empresa.
  - Validación de backend asegurando que el tutor laboral pertenezca a la empresa elegida.
  - Cómputo automático de horas por defecto: precarga las horas acreditadas en 1º curso (`horas_practicas_primero`).
  - Anotación informativa destacada bajo el campo de horas indicando dónde realizó las prácticas de 1º el alumno y cuántas horas fueron.
  - Vinculación automática con la oferta: al crear la práctica se asocia `oferta_id`, la solicitud pasa a estado `aceptado` y se redirige con confirmación al detalle de la oferta.
- **Validación y Pruebas**:
  - Tests unitarios y de integración añadidos y verificados en `PracticaModuleTest` y `EmpresaModuleTest`.
  - Validación `scripts/init.sh` superada con éxito.

## 2026-10-21 — mensajeria-coord-admin: Sistema de mensajería oficial para Coordinador Dual y Administradores

- **Sistema de mensajería multicanal (In-App y Email Corporativo)**:
  - `database/migrations/2026_10_21_130000_create_mensajes_tables.php`:
    - Tabla `mensajes`: remitente (`remitente_id`), asunto, cuerpo, tipo de destinatario (`tipo_destinatario`), resumen de destinatarios y recuento total.
    - Tabla `mensaje_destinatarios`: relación muchos a muchos entre mensajes y usuarios (`mensaje_id`, `user_id`, `leido_at` timestamp).
  - `app/Models/Mensaje.php` y `app/Models/User.php`:
    - Modelo `Mensaje` con relaciones `remitente()`, `destinatarios()`, `destinatariosLeidos()`, `destinatariosPendientes()`, y métodos `esLeidoPor()`, `marcarLeidoPor()`.
    - Relaciones `mensajesEnviados()` y `mensajesRecibidos()` en `User`.
  - `app/Providers/AuthServiceProvider.php`:
    - Gate `enviar-mensajes`: autoriza a usuarios con rol `admin` o `coordinador_dual`.
    - Gate `ver-mensaje`: autoriza al remitente, coordinadores duales, administradores y a cualquier usuario destinatario del mensaje.
  - `app/Http/Controllers/MensajeController.php`:
    - `index()`: listado paginado de comunicados con métricas de lecturas en tiempo real (`X / Y leídos` y porcentaje).
    - `create()`: interfaz interactiva de redacción con 3 modalidades de destinatarios:
      1. **Grupos completos (uno o varios)**: resuelve todos los alumnos activos matriculados en los grupos elegidos.
      2. **Alumnos seleccionados dentro de un grupo**: selector de grupo y lista dinámica en Alpine.js con buscador en tiempo real, selección individual y botones para marcar/desmarcar todos.
      3. **Profesorado de ciclos formativos**: selección de uno o varios ciclos, resolviendo docentes por asignaturas impartidas, docencia en líneas/grupos, tutorías de ciclo y familia profesional.
    - `store()`: validación exhaustiva, deduplicación de usuarios, almacenamiento, vinculación en tabla pivote y despacho dual (in-app + email) a cada destinatario.
    - `show()`: detalle del comunicado. Si el usuario actual es destinatario, marca automáticamente `leido_at = now()` en la tabla pivote y actualiza el estado de la notificación in-app (`es_leida = true`). Si es gestor o remitente, despliega panel de métricas con gráfico circular y tabla de trazabilidad de lecturas por usuario con fecha y hora.
  - `app/Mail/NotificacionMailable.php` y `resources/views/emails/notificacion.blade.php`:
    - Mailable dedicado con asunto estandarizado `Nueva notificación de Gestión DUAL H.Lanz: {Título}`.
    - Plantilla HTML responsiva corporativa con logotipo oficial del IES Hermenegildo Lanz, aviso de nueva notificación, enlace directo a notificaciones y al mensaje, cuerpo del comunicado y pie con aviso legal RGPD.
  - Interfaz y Navegación:
    - Enlace a "Mensajería" en el sidebar (`resources/views/layouts/app.blade.php`) para coordinadores duales y administradores.
    - Botón "Redactar comunicado" e icono distinguido para mensajes en `resources/views/notificaciones/index.blade.php`.
- **Tests Automatizados**:
  - `tests/Feature/MensajeriaTest.php`: 8 pruebas automáticas (42 aserciones) verificando acceso por rol, bloqueo a usuarios no autorizados, envío a grupos, envío selectivo a alumnos, envío a profesores de ciclos, marcado de lectura y despacho de emails institucionales con asunto oficial. 100% en verde.

## 2026-10-21 — oferta-compartir-token: Compartir ofertas con la empresa mediante enlace seguro con token

- **Acceso seguro sin usuario para empresas colaboradoras**:
  - `database/migrations/2026_10_21_120000_add_token_to_ofertas_practicas_table.php`:
    - Columna `token` (`string(64) nullable unique`) en `ofertas_practicas`.
    - Backfill automático para ofertas existentes generando tokens criptográficamente seguros de 40 caracteres (`Str::random(40)`).
  - `app/Models/OfertaPractica.php`:
    - Atributo `'token'` en `$fillable`.
    - Evento `creating` en `booted()` para asignación automática de token en cada nueva oferta.
    - Método `regenerarToken(): string` para invalidar el token anterior y emitir uno nuevo ante necesidad de revocación.
  - `routes/web.php` y `app/Http/Controllers/OfertaPracticaController.php`:
    - Rutas públicas seguras: `GET /ofertas/compartida/{token}`, `GET /ofertas/compartida/{token}/alumnos/{alumno}/cv`, `GET /ofertas/compartida/{token}/descargar-cvs`.
    - Ruta autenticada: `POST /ofertas/{oferta}/regenerar-token`.
    - Métodos `compartida()`, `descargarCvAlumnoCompartida()` y `descargarCvsZipCompartida()` con sanitización de descargas y cumplimiento RGPD.
  - `resources/views/ofertas/compartida.blade.php`:
    - Portal público para la empresa con diseño Stitch UI, branding institucional del IES Hermenegildo Lanz, estado de la oferta, número de plazas, especialidad requerida, candidatos asignados con foto, descarga individual de CVs y descarga masiva en ZIP, junto con aviso legal RGPD y banner de cookies.
  - `resources/views/ofertas/show.blade.php`:
    - Tarjeta "Compartir oferta con la empresa (sin usuario registrado)" con el enlace completo, botón interactivo para copiar al portapapeles con un clic, acceso directo y opción para regenerar el token de seguridad.
- **Tests Automatizados**:
  - `tests/Feature/OfertaPracticaTest.php`: 5 pruebas automáticas específicas verificando acceso por token sin autenticación, descarga individual y en ZIP, rechazo 404 de tokens inválidos y revocación/regeneración de tokens. 100% en verde.

## 2026-10-21 — oferta-descarga-cvs: Descarga individual y masiva en ZIP de currículums (CV) en la vista de ofertas

- **Descarga individual y masiva en ZIP de CVs en Ofertas de Prácticas (`ofertas.show`)**:
  - `routes/web.php`:
    - Añadida ruta `GET /ofertas/{oferta}/descargar-cvs` (`ofertas.descargar-cvs`) para descarga de todos los CVs en ZIP.
    - Añadida ruta `GET /ofertas/{oferta}/alumnos/{alumno}/cv` (`ofertas.descargar-cv-alumno`) para descarga del CV individual de cada alumno solicitante o asignado.
  - `app/Http/Controllers/OfertaPracticaController.php`:
    - `show()`: carga anticipada optimizada (`solicitudes.alumno.user`), cálculo de permisos y contador reactivo de CVs disponibles (`$totalCvsDisponibles`).
    - `canDescargarCvs()`: control estricto de acceso y privacidad RGPD que permite la descarga a administradores, profesores, coordinadores duales, creadores de la oferta y representantes de empresa autorizados, bloqueando el acceso a alumnos u otros roles no autorizados.
    - `descargarCvAlumno()`: valida pertenencia a la oferta, existencia de archivo en `Storage::disk('public')`, sanitiza el nombre de descarga (`CV_nombre_alumno.ext`) y sirve la descarga directa.
    - `descargarCvsZip()`: agrupa todos los CVs subidos de los alumnos postulados o asignados a la oferta, genera dinámicamente un archivo ZIP con `ZipArchive` asignando nombres descriptivos sin colisiones (`CV_nombre_alumno.ext`), y lo descarga con limpieza automática temporal (`deleteFileAfterSend(true)`). Si no hay CVs subidos, redirige informando mediante mensaje de aviso.
  - `resources/views/ofertas/show.blade.php`:
    - Mensajes y alertas de sesión (`session('success')`, `session('warning')`, `$errors`).
    - En la cabecera de "Alumnado Asignado y Solicitudes": badge con el número de CVs disponibles y botón destacado "Descargar todos los CVs (ZIP)" con icono y estilo corporativo (o botón desactivado informativo si ningún candidato ha subido CV aún).
    - En la tabla de candidatos: columna dedicada "Currículum (CV)" con botón "Descargar CV" para descarga directa del documento formateado y botón rápido para abrir/previsualizar en pestaña nueva. En caso de no haber subido archivo, muestra indicador "Sin CV subido".
  - **Tests Automatizados**:
    - Añadidos 5 tests en `tests/Feature/OfertaPracticaTest.php`:
      - `test_oferta_show_displays_cv_column_and_buttons`: valida visualización de columna, badges y botones.
      - `test_profesor_can_download_individual_student_cv_from_oferta`: valida descarga individual con cabeceras correctas.
      - `test_profesor_can_download_all_cvs_as_zip_from_oferta`: valida compresión y descarga en ZIP.
      - `test_zip_download_redirects_with_warning_when_no_cvs_available`: valida redirección con aviso si no hay CVs.
      - `test_alumno_cannot_download_cvs_or_zip_from_oferta`: verifica bloqueo 403 (RGPD/privacidad) para alumnos.
    - Suite de 36 pruebas en `OfertaPracticaTest` pasando al 100%.

## 2026-10-20 — coord-dual-alumnos-acceso: Acceso global del Coordinador Dual a todo el alumnado, expedientes y archivos subidos

- **Visibilidad y Acceso Integral del Coordinador Dual al Alumnado**:
  - `app/Models/User.php`:
    - Enriquecido el método `hasRole(string $role)` para verificar tanto el array JSON de `roles` como el atributo booleano `profesor->es_coordinador_dual`, garantizando que cualquier usuario designado como Coordinador Dual en la ficha docente tenga acceso inmediato a todas las capacidades de su rol.
  - `app/Http/Controllers/ProfesorController.php`:
    - En `store()` y `update()`, se sincroniza automáticamente el rol `User::ROLE_COORDINADOR_DUAL` en el array de `roles` del usuario cada vez que se marca o desmarca la casilla de Coordinador Dual.
  - `app/Http/Controllers/AlumnoController.php`:
    - En `index()`: se confirma que los coordinadores duales no sufren restricción por grupos docentes, visualizando la totalidad del alumnado matriculado en el centro.
    - En `show(Alumno $alumno)`: carga anticipada de relaciones completas, incluyendo `proyectos.ciclo`, `proyectos.cursoAcademico` y `proyectos.imagenes` para dar acceso a todas las evidencias digitales y documentos del alumno.
  - `app/Policies/UserPolicy.php`:
    - Permite a los coordinadores duales desactivar y reactivar alumnos (`deactivate`) en cumplimiento de los permisos otorgados en los gates de autorización.
  - `app/Http/Controllers/ProyectoController.php`:
    - En `index()`: los usuarios con rol `coordinador_dual` tienen visibilidad global sobre todos los proyectos de los alumnos del centro (igual que los administradores).
- **Interfaz de Usuario y Gestión de Archivos**:
  - `resources/views/alumnos/index.blade.php`:
    - Protegido el botón "Nuevo Alumno" con `@can('create-alumno')`, permitiendo su uso tanto al administrador como al coordinador dual.
    - Protegidas las acciones de desactivación y reactivación con `@can('deactivate-alumno')`.
  - `resources/views/alumnos/show.blade.php`:
    - Incorporada sección "Archivos y Documentación Subida": tarjeta de descarga directa del Currículum Vitae (CV) y visualizador del avatar/foto de perfil.
    - Incorporada sección "Proyectos y Evidencias Digitales": galería de proyectos del alumno con descripción, ciclo, curso, calificaciones, enlaces a repositorio y demo, y visor/descarga de todas las capturas y archivos de proyecto subidos (`proyecto_imagenes`).
    - Añadido acceso directo en cabecera a las observaciones y seguimiento tutorial (`anotaciones.show`).
- **Tests Automatizados**:
  - En `tests/Feature/AlumnoModuleTest.php`: añadido `test_coordinador_dual_can_view_all_alumnos_and_access_files` validando que el coordinador dual ve alumnos de grupos ajenos, accede a sus perfiles y archivos (CV, fotos, proyectos e imágenes), accede a creación/edición, desactiva alumnos y consulta todos los proyectos institucionales.

## 2026-10-20 — prof-view-alumno: Acceso docente a perfil e información de alumnos y botón de descarga de CV en listado

- **Acceso del profesorado al perfil e información de alumnos**:
  - `app/Providers/AuthServiceProvider.php`: actualizado el gate `view-alumno` para permitir acceso directo a usuarios con rol `profesor` (`User::ROLE_PROFESOR`). Anteriormente, verificaba un campo `$alumno->grupo_id` inexistente en la tabla `alumnos` y solo grupos de tutoría, lo que bloqueaba a todos los profesores con error 403.
  - `app/Http/Controllers/AlumnoController.php`: en `show(Alumno $alumno)` se cargan de forma anticipada las relaciones completas (`user`, `grupos.linea.ciclo.familia`, `grupos.cursoAcademico`, `grupos.tutor`, `tutorPracticas`, `ciclosMatriculados`, `practicas.empresa`, `practicas.oferta`, `practicas.cursoAcademico`, `practicas.tutorLaboral`). Además, se corrigió el filtro de `index()` para asegurar que usuarios con rol `coordinador_dual` conserven visibilidad global.
  - `app/Models/Alumno.php`: añadida relación `practicas(): HasMany` con `Practica::class`.
  - `resources/views/alumnos/show.blade.php`: enriquecida la vista de perfil para mostrar la información completa del expediente (datos de contacto y enlaces, grupos con tutor/a, ciclos formativos matriculados, estado, prácticas de 1º curso, prácticas / FCT en empresas y acceso a observaciones). Los enlaces de edición quedan protegidos mediante `@can('update-alumno', $alumno)`.
  - `app/Http/Controllers/AnotacionController.php`: en `show()` se permite ver observaciones al equipo educativo completo del alumno (tutores de aula y profesores que imparten docencia en sus grupos), cumpliendo el requisito RF7 del spec.
- **Botón de descarga de CV en el listado de alumnos**:
  - `resources/views/alumnos/index.blade.php`:
    - Añadido botón de acción directa de descarga de CV con icono y tooltip en la columna de acciones de la tabla de escritorio cuando `$alumno->user->cv_url` está presente.
    - Añadido enlace destacado `CV` bajo el nombre del alumno junto a LinkedIn y Portfolio.
    - Añadido botón "Descargar CV" en las tarjetas móviles responsivas.
    - Protegida la acción de editar con `@can('update-alumno', $alumno)` en escritorio y móvil.
- **Tests Automatizados**:
  - En `tests/Feature/AlumnoModuleTest.php`:
    - `test_profesor_can_view_alumno_profile_and_info`: valida que un usuario profesor puede acceder a `alumnos.show`, consultar los datos completos del alumno y descargar su CV sin ver enlaces de edición no autorizados.
    - `test_alumnos_index_shows_cv_download_button_when_cv_is_present`: valida que en el listado de alumnos se renderiza el botón de descarga cuando el alumno tiene subido su archivo de CV.
  - 100% de tests pasando en `AlumnoModuleTest`, `AnotacionModuleTest`, `PaginationAndFilterTest` y `AlumnoPracticasPrimeroTest`.

## 2026-10-15 — fix-profile-413: Corrección de falso Error 413 al guardar datos sin subir archivos en edición de perfil

- **Causa raíz identificada**:
  - En [`update-profile-information-form.blade.php`](file:///var/www/html/ieshlanz/resources/views/profile/partials/update-profile-information-form.blade.php), el manejador `@submit="handleSubmit"` interceptaba de manera incondicional cualquier envío de formulario vía `fetch()`.
  - En entornos Docker tras proxy inverso, `TrustProxies::$proxies` estaba en `null`, lo que provocaba que Laravel no detectara la conexión HTTPS y retornara redirecciones 302 hacia URLs `http://`.
  - El navegador bloqueaba la petición `fetch()` por *Mixed Content* o rechazo de red, y el bloque `.catch()` asignaba erróneamente un mensaje acusatorio de "posible Error 413: archivo demasiado grande" e indicaba límites de avatar/CV aunque no se hubiese adjuntado ningún archivo.
  - Además, `ProfileController::update` tenía declarado como tipo de retorno exclusivamente `RedirectResponse`, provocando un `TypeError` cuando se solicitaban respuestas en formato JSON.
- **Solución implementada**:
  - `resources/views/profile/partials/update-profile-information-form.blade.php`:
    - Si no se han seleccionado archivos en los inputs (`!hasAvatar && !hasCv`), se permite el envío estándar nativo del formulario HTML sin intercepción de `fetch()`, garantizando la actualización limpia y transparente de campos de texto como `empresa_practicas_primero`, `horas_practicas_primero`, etc.
    - El banner dinámico de error solo muestra los límites de Avatar (2 MB) y CV (5 MB) y la etiqueta "Error 413" si efectivamente ocurrió un error de archivo (`isUploadError`).
    - Si `fetch()` encuentra un error de red o redirección, realiza un fallback automático enviando el formulario nativamente con `form.submit()`.
  - `app/Http/Middleware/TrustProxies.php`:
    - Configurado `protected $proxies = '*';` para reconocer los encabezados `X-Forwarded-Proto` y asegurar que todas las URLs y redirecciones sean siempre HTTPS tras Nginx/Proxy Manager.
  - `app/Http/Controllers/ProfileController.php`:
    - Actualizada la firma de `update` a `RedirectResponse|JsonResponse` y retorno de respuesta JSON `{"status": "profile-updated"}` ante peticiones AJAX/JSON.
  - `tests/Feature/ProfileError413AndIconTest.php`:
    - Añadida prueba `test_profile_update_without_files_returns_json_success_when_requested_via_ajax` validando el guardado correcto de `empresa_practicas_primero` y `horas_practicas_primero` sin error 413.

## 2026-10-15 — ui-proyectos-error413: Cambio de icono de Módulo de Proyectos (2º) y captura explicativa de Error 413 en perfil

- **Actualización de icono de "Módulo de Proyectos (2º)"**:
  - Reemplazado el icono anterior (pieza de puzzle ambigua `M19.428...`) por el icono reconocible y estilizado de lanzamiento / cohete e innovación (`rocket-launch`).
  - Actualizado en:
    - Barra de navegación principal y menú lateral ([`resources/views/layouts/app.blade.php`](file:///var/www/html/ieshlanz/resources/views/layouts/app.blade.php)).
    - Tarjeta superior de Proyectos de 2º en el Dashboard ([`resources/views/dashboard.blade.php`](file:///var/www/html/ieshlanz/resources/views/dashboard.blade.php)).
    - Tarjeta de acceso rápido a Repositorio de Proyectos en el Dashboard ([`resources/views/dashboard.blade.php`](file:///var/www/html/ieshlanz/resources/views/dashboard.blade.php)).
    - Estado vacío del listado de proyectos ([`resources/views/proyectos/index.blade.php`](file:///var/www/html/ieshlanz/resources/views/proyectos/index.blade.php)).
- **Captura y explicación detallada de Error 413 (Payload Too Large) en Edición de Perfil**:
  - `resources/views/profile/partials/update-profile-information-form.blade.php`:
    - Componente interactivo Alpine.js (`profileEditForm()`) con validación en tiempo real en los eventos `@change` de los inputs de avatar y CV.
    - Detección precisa de tamaño excedido (Avatar > 2 MB, CV > 5 MB) y formato de archivo no compatible (Avatar: JPG, PNG, WebP; CV: PDF, JPG, PNG, WebP).
    - En el envío del formulario (`handleSubmit`), intercepta la petición vía `fetch()` para capturar respuestas HTTP 413 (Payload Too Large) tanto de Nginx como del servidor PHP.
    - Muestra un banner explicativo destacado en color rojo/ámbar explicando detalladamente si el error se debe a tamaño excesivo o formato inadecuado, sin perder el resto de los datos introducidos.
    - Soporte completo para sesiones normales mediante flash banner `@if(session('error_413') || $errors->has('file_upload_error'))`.
  - `app/Exceptions/Handler.php`:
    - Registrado callback renderable para `PostTooLargeException` y `HttpException` (código 413).
    - Para peticiones AJAX/JSON: responde con JSON estructurado con código 413, mensaje explicativo y desglose de límites y formatos permitidos.
    - Para peticiones estándar: redirige de vuelta con `withErrors(['file_upload_error' => ...])` y flash `error_413` explicativo.
  - `resources/views/errors/413.blade.php`:
    - Página de error 413 dedicada con diseño Stitch UI que detalla los límites permitidos, formatos soportados y consejos para optimizar o comprimir los archivos antes de subirlos, con botones directos para volver a "Mi Perfil" o al Dashboard.
- **Tests Automatizados**:
  - `tests/Feature/ProfileError413AndIconTest.php`: 6 pruebas automáticas (31 aserciones) verificando:
    1. Renderizado de Proyectos sin el icono antiguo en sidebar y dashboard.
    2. Renderizado de instrucciones de límites y formatos en el perfil.
    3. Respuesta JSON 413 ante `PostTooLargeException` con explicación informativa.
    4. Redirección con flash `error_413` ante `PostTooLargeException` en peticiones estándar.
    5. Renderizado correcto de la vista dedicada `errors/413.blade.php`.
    6. Validación de tamaños máximos y formatos en `ProfileUpdateRequest`.
  - 100% de los tests pasando.

## 2026-10-15 — pag-001: Paginación de listados (alumnos, profesores, prácticas, empresas) y filtrado por grupo en alumnado

- **Paginación estándar a 15 elementos por página**:
  - `AlumnoController`: modificado para ordenar en base de datos (`users.name` ASC mediante JOIN con `users`), paginar a 15 elementos por página (`$query->paginate($perPage)->withQueryString()`) y soportar parámetro `per_page` (10, 15, 25, 50).
  - `ProfesorController`: ordenación en base de datos por `users.name` ASC y paginación a 15 elementos por página con preservación de parámetros mediante `withQueryString()`.
  - `PracticaController`: ordenación descendente por fecha de inicio y paginación a 15 elementos por página con `withQueryString()`.
  - `EmpresaController`: paginación estandarizada de 50 a 15 elementos por página con `withQueryString()`.
  - `OfertaPracticaController` y `ProyectoController`: homogeneizados con paginación a 15 elementos y `withQueryString()`.
- **Filtro interactivo por grupo en Alumnado**:
  - `resources/views/alumnos/index.blade.php`: incorporado selector `<select name="grupo_id">` en la barra de herramientas de filtrado con los grupos disponibles (respetando los grupos asignados al docente si no es admin).
  - El botón "Limpiar" detecta automáticamente si `grupo_id` está activo para permitir reiniciar los filtros.
  - `AlumnoController`: aplica el filtro `$query->whereHas('grupos', fn($q) => $q->where('grupos.id', $grupoId))` y carga `$grupos` para el selector.
- **Tests Automatizados**:
  - Creado `tests/Feature/PaginationAndFilterTest.php` con 5 tests específicos:
    - `test_alumnos_list_is_paginated_to_15_per_page`
    - `test_alumnos_can_be_filtered_by_grupo`
    - `test_profesores_list_is_paginated_to_15_per_page`
    - `test_practicas_list_is_paginated_to_15_per_page`
    - `test_empresas_list_is_paginated_to_15_per_page`
  - 100% de los tests pasando.

## 2026-10-15 — alum-002: Registro de empresa y horas de prácticas de 1º curso para alumnado de 2º

- **Migración de base de datos**:
  - `database/migrations/2026_10_15_180000_add_practicas_primero_to_alumnos_table.php`: añade las columnas `empresa_practicas_primero` (`string(255) nullable`) y `horas_practicas_primero` (`unsignedInteger nullable`) a la tabla `alumnos`.
  - Migración ejecutada con éxito en la base de datos de producción (`docker exec ieshlanz-app php artisan migrate --force`).
- **Modelo Alumno y Modelo Practica**:
  - `app/Models/Alumno.php`: campos añadidos a `$fillable`, cast `'horas_practicas_primero' => 'integer'`, y método helper `esSegundoCurso(): bool` para identificar si el alumno pertenece a un grupo de 2º curso.
  - `app/Models/Practica.php`: `totalHorasAlumno()` suma automáticamente las horas de 1º curso (`horas_practicas_primero`) al cómputo total de horas acumuladas entre 1º y 2º hacia las 500h mínimas.
- **Formulario de Perfil del Alumno (`profile.edit`)**:
  - `app/Http/Requests/ProfileUpdateRequest.php` y `app/Http/Controllers/ProfileController.php`: validación (`max:255`, `min:0, max:2000`) y actualización de `empresa_practicas_primero` y `horas_practicas_primero` sobre el modelo `Alumno` del usuario autenticado.
  - `resources/views/profile/partials/update-profile-information-form.blade.php`: tarjeta dedicada con diseño Stitch UI que se muestra para alumnos, destacando con badge a quienes están matriculados en 2º curso para que introduzcan empresa y horas de 1º.
- **Formulario de Auto-registro de Alumnos (`/register`)**:
  - `app/Http/Controllers/Auth/RegisteredUserController.php`: validación y almacenamiento de los datos de prácticas de 1º al registrar al alumno.
  - `resources/views/auth/register.blade.php`: reactividad con Alpine.js (`x-show="tieneSegundo"`) que detecta automáticamente si el alumno marca cualquier grupo de 2º curso y despliega de inmediato la sección opcional para registrar empresa y horas de 1º.
- **Gestión Docente y Administrativa de Alumnos (CRUD)**:
  - `resources/views/alumnos/create.blade.php` y `resources/views/alumnos/edit.blade.php`: campos incorporados para la gestión por parte de administradores y tutores.
  - `resources/views/alumnos/show.blade.php`: tarjeta informativa de "Prácticas de 1º Curso" con badge de 2º curso, nombre de la empresa y horas computables.
  - `resources/views/practicas/mis-practicas.blade.php`: tarjeta resumen superior que muestra a los alumnos de 2º curso sus prácticas de 1º y el cómputo global de horas acumuladas (1º + 2º) sobre el objetivo de 500 horas.
- **Tests Automatizados**:
  - `tests/Feature/AlumnoPracticasPrimeroTest.php`: 5 pruebas completadas con 28 aserciones cubriendo visualización/edición en perfil, guardado en registro con grupo de 2º, gestión completa en CRUD de alumnos, cálculo acumulado en `Practica::totalHorasAlumno()`, y rechazo de horas negativas.

## 2026-10-15 — fix: Recuperación íntegra de la base de datos mediante Binary Logs y aislamiento total del entorno de test

- **Recuperación completa Point-in-Time desde MySQL Binary Logs (`binlog.000004`)**:
  - Al ejecutar tests con `RefreshDatabase` que detectaban migraciones pendientes, Laravel ejecutaba `migrate:fresh` sobre la base de datos de desarrollo.
  - Gracias al registro binario activo de MySQL (`binlog.000001` a `binlog.000004`), se localizaron todas las transacciones previas a las 14:30 (posición 413415, previa al `DROP TABLE`).
  - Se reconstruyó y restauró el 100% de los datos:
    - 25 usuarios reales (Nacho Cabrero, Adrián Trujillo, Daniel Villegas, Sara Jimenez, Vicente Ruiz, etc.).
    - 23 alumnos registrados con sus datos completos.
    - 22 grupos, 5 empresas, 4 ofertas de prácticas y más de 700 registros de auditoría (`cambios`).
  - Guardada copia de seguridad de seguridad en [`storage/backup_recovered_20261015.sql`](file:///var/www/html/ieshlanz/storage/backup_recovered_20261015.sql).
- **Aislamiento definitivo e inviolable del entorno de Testing**:
  - Se creó la base de datos independiente `ieshlanz_testing` en MySQL.
  - [`config/database.php`](file:///var/www/html/ieshlanz/config/database.php) blindado: si `APP_ENV === 'testing'`, la conexión MySQL apunta forzosamente a `ieshlanz_testing`.
  - [`phpunit.xml`](file:///var/www/html/ieshlanz/phpunit.xml) actualizado con `force="true"` apuntando a `ieshlanz_testing`.
  - Es físicamente imposible que una suite de pruebas vuelva a tocar o refrescar la base de datos principal `ieshlanz`.

- **Restauración y blindaje permanente de `icabrero@ieshlanz.es`**:
  - Se identificó que al ejecutarse `migrate:fresh` o refresco de base de datos durante las pruebas automatizadas, el usuario `icabrero@ieshlanz.es` se perdía debido a que no formaba parte de las migraciones base ni de los seeders iniciales.
  - Creada y ejecutada la migración [`2026_10_15_160000_ensure_admin_user_icabrero.php`](file:///var/www/html/ieshlanz/database/migrations/2026_10_15_160000_ensure_admin_user_icabrero.php):
    - Crea/actualiza `icabrero@ieshlanz.es` con contraseña `nippon12`.
    - Asigna roles `admin`, `profesor` y `coordinador_dual`.
    - Establece `is_active = true`, `registration_status = 'approved'` y consentimientos RGPD.
    - Asocia ficha de `Profesor` en la Familia de Informática con tutoría de `1º DAW - Mañana` y docencia en `1º DAW` y `2º DAW`.
  - Actualizado [`DatabaseSeeder.php`](file:///var/www/html/ieshlanz/database/seeders/DatabaseSeeder.php) con lógica idempotente (`firstOrCreate`) que incluye permanentemente a `icabrero@ieshlanz.es`.
- **Tests**:
  - Añadido `test_admin_icabrero_can_authenticate` en [`tests/Feature/Auth/AuthenticationTest.php`](file:///var/www/html/ieshlanz/tests/Feature/Auth/AuthenticationTest.php), validando la autenticación directa y verificación de rol de administrador.

## 2026-10-15 — fix: Vinculación de grupos al curso actual y asignación de tutoría/docencia en edición de profesores

- **Creación y activación de curso académico actual y vinculación de grupos**:
  - Migración `2026_10_15_150000_ensure_curso_academico_actual_and_link_grupos.php` ejecutada. Garantiza la existencia del curso académico activo actual (`2026-2027`) y vincula todos los grupos, `alumno_grupo`, `ofertas_practicas` y `practicas` que tenían `curso_academico_id = null`.
  - `AcademicStructureSeeder`: actualizado para crear siempre el curso académico `2026-2027` activo y asignar `curso_academico_id` a los grupos generados.
- **Controlador de Profesores (`ProfesorController`)**:
  - `edit()`:
    - Recupera el curso académico activo con fallback al más reciente o generación preventiva del curso actual `2026-2027`.
    - Inferencia automática de la familia profesional del profesor a través de sus grupos impartidos si no la tiene asignada.
    - `$gruposCursoActual` incluye grupos con `curso_academico_id = $cursoActual->id` o con `curso_academico_id IS NULL`.
    - `$historial` solo agrupa asignaturas de cursos pasados explícitos distintos del curso activo, evitando que los grupos huérfanos terminen erróneamente en el historial de sólo lectura.
  - `update()`:
    - Asegura que al guardar docencia y tutorías, los grupos seleccionados queden asignados al curso académico activo si no lo estaban.
- **Vista de Edición de Profesor (`resources/views/profesores/edit.blade.php`)**:
  - Asegurada la opción del grupo del cual es tutor en el desplegable incluso si pertenece a otra familia o fue asignado previamente.
- **Controladores de Estructura y Registro**:
  - `GrupoController` y `LineaController`: asegurada asignación por defecto del curso académico activo a nuevos grupos.
  - `TeacherRegistrationController` y `RegisteredUserController`: soporte para grupos con `curso_academico_id = $cursoActivo->id` o `IS NULL`.
- **Tests**:
  - `tests/Feature/ProfesorModuleTest.php`: añadidos tests para validar que un profesor registrado ve los grupos de su familia en el curso actual sin advertencias de ausencia de grupos, y que puede asignarse tutoría y docencia con asignaturas.

## 2026-10-15 — fix & feat: Acceso a Gestión de Prácticas para profesores y selección de tutoría/docencia en /teacher

- **Acceso y autorización en Gestión de Prácticas (`PracticaController`)**:
  - Resuelto el error HTTP 403 que sufrían los profesores al acceder a `/practicas` (`practicas.index`).
  - Autorización extendida en `index`, `create`, `store`, `show`, `edit`, `update` y `actualizarHoras` para aceptar `ROLE_ADMIN`, `ROLE_COORDINADOR_DUAL` y `ROLE_PROFESOR`.
  - Ámbito de visualización contextualizado para docentes: si el usuario es profesor (y no admin/coordinador), la consulta se filtra automáticamente a las prácticas de sus alumnos (tutor de prácticas, tutor de grupo o docente en los grupos del alumno).
  - Si un profesor no tiene grupos asignados aún, accede limpiamente a la vista mostrándose 0 prácticas en lugar de un error 403.
  - Ocultado el botón de borrado definitivo a usuarios no administradores en `practicas/index.blade.php`.
- **Acceso en Prácticas por Grupo (`GrupoPracticaController`)**:
  - Incorporada comprobación de `tutor_id` del grupo en `index` y `show`, asegurando que los profesores tutores de aula tengan acceso directo al seguimiento de su grupo.
- **Selección de Tutoría y Grupos de Docencia en Auto-registro Docente (`/teacher`)**:
  - `TeacherRegistrationController`:
    - `create()`: carga la lista de grupos activos del curso actual.
    - `store()`: valida y guarda `tutor_grupo_id` (dropdown opcional) y `grupos` (array de checkboxes opcionales).
    - Asigna `es_tutor = true` en el modelo `Profesor` y establece el `tutor_id` en la tabla `grupos` si fue seleccionado.
    - Sincroniza los grupos en los que imparte clase en la tabla pivote `grupo_profesor`.
  - `resources/views/auth/register-teacher.blade.php`:
    - Incorporado selector desplegable "¿Eres tutor de algún grupo? (Optativo)".
    - Incorporado grid responsivo con scroll de checkboxes "Grupos a los que impartes clase (Optativo)".
- **Visualización en Panel de Administración (`/admin/users`)**:
  - `UserController`: eager loading de `profesor.gruposTutor` y `profesor.gruposImpartidos`.
  - `admin/users/index.blade.php`: muestra badges informativos indicando el grupo del que es tutor y los grupos donde imparte docencia.
- **Configuración de usuario de prueba `icabrero@ieshlanz.es`**:
  - Contraseña actualizada a `nippon12`.
  - Rol `profesor`, activo y aprobado.
  - Asignado como tutor de `1º DAW - Mañana` y docente en `1º DAW - Mañana` y `2º DAW - Mañana`.
- **Tests automatizados**:
  - `tests/Feature/PracticaModuleTest.php`: actualizados tests para verificar que los profesores acceden a `practicas.index` (código HTTP 200), viendo solo a sus alumnos y permitiendo el acceso sin 403 incluso sin grupos vinculados.
  - `tests/Feature/TeacherRegistrationTest.php`: añadidos tests para validar la presentación de grupos en la vista `/teacher` y el registro completo con grupo tutor y grupos impartidos.

## 2026-10-15 — fix: Campo portfolio externo opcional y oculto en la ficha si está en blanco

- **Formulario de edición de perfil (`profile/partials/update-profile-information-form.blade.php`)**:
  - Etiqueta indicada explícitamente como "Portfolio Externo / Web Personal (Opcional)".
  - Valor por defecto vacío (`old('portfolio_url', $user->portfolio_url)`) con placeholder orientativo, sin forzar `https://` dentro del valor cuando no existe portfolio.
  - Texto de ayuda aclarando que si se deja en blanco no se mostrará en la ficha del usuario.
- **Validación y sincronización de datos**:
  - `ProfileUpdateRequest`: `prepareForValidation` convierte cadenas vacías, espacios o sólo protocolo (`https://`, `http://`) a `null`. Si el usuario ingresa un dominio sin protocolo, se autocompleta con `https://`.
  - `ProfileController`: al guardar el perfil del usuario, sincroniza la actualización de `portfolio_url`, `linkedin_url` y `telefono` con el registro de `Alumno` si existe, garantizando que el borrado a `null` quede reflejado en ambas tablas.
- **Visualización en la ficha del usuario (`alumnos/show.blade.php`)**:
  - La sección completa de "Portfolio Externo / Web Personal" se envuelve en `@if($portfolio) ... @endif`, de modo que si el campo está en blanco no se muestra en la ficha de usuario (desapareciendo por completo en lugar de mostrar "No indicado").
- **Tests**:
  - Añadido `test_portfolio_is_hidden_from_user_card_when_blank` en `tests/Feature/ProfileTest.php`.

## 2026-10-15 — reg-002: Auto-registro de profesores en /teacher y blindaje del estado activo

- **Portal exclusivo de registro de profesorado (`/teacher`)**:
  - `routes/auth.php`: rutas `GET /teacher` (`teacher.register`) y `POST /teacher` (`teacher.register.store`) disponibles para usuarios invitados (`guest`).
  - `TeacherRegistrationController`:
    - `create()`: carga familias profesionales activas y sirve `auth.register-teacher`.
    - `store()`: valida nombre, email institucional/personal único, contraseña confirmada, especialidad docente, familia profesional y consentimiento obligatorio de RGPD y Aviso de Privacidad.
    - Crea la cuenta con rol `profesor`, `is_active = false` y `registration_status = 'pending'`, vinculando el registro en la tabla `profesores`.
    - No inicia sesión automáticamente; redirige al login con notificación explicativa de validación directiva requerida.
- **Vista dedicada `resources/views/auth/register-teacher.blade.php`**:
  - Diseño Stitch UI con badge "Portal del Profesorado", tipografía y contrastes de alta legibilidad (`text-slate-800`), aviso explicativo sobre la verificación previa de cuentas docentes por jefatura/administración, selector de familia profesional, campo de especialidad docente y checkboxes RGPD conforme a normativa europea.
  - Enlaces de navegación cruzada en `resources/views/auth/register.blade.php` ("¿Eres profesor/a? Accede al registro docente aquí") y en `resources/views/auth/login.blade.php`.
- **Blindaje del estado activo y visibilidad del estado de espera**:
  - `app/Models/User.php`: endurecido `isActive()` para devolver de forma estricta `false` cuando el usuario se encuentre en estado `pending` o `rejected`.
  - `app/Http/Requests/Auth/LoginRequest.php`: bloqueo explícito con mensajes específicos en caso de intento de acceso con cuenta pendiente o rechazada.
  - `resources/views/alumnos/index.blade.php`, `resources/views/alumnos/show.blade.php`, `resources/views/profesores/index.blade.php` y `resources/views/profesores/show.blade.php`: incorporados badges `⏳ En espera` (amber) cuando el usuario tiene registro pendiente de aprobación, evitando que aparezcan erróneamente como activos.
- **Gestión y aprobación en Panel de Administración (`/admin/users`)**:
  - `UserController::index()`: incorpora eager loading de `profesor.familia`.
  - `resources/views/admin/users/index.blade.php`: columna "Grupos / Especialidad" adaptada para mostrar la especialidad y familia profesional en filas de profesorado, permitiendo al administrador verificar sus datos y pulsar "Aceptar" (`approve`) o "Rechazar" (`reject`).
- **Tests automatizados**:
  - `tests/Feature/TeacherRegistrationTest.php`: 7 tests unitarios y de integración que verifican la carga de la vista, filtrado de familias activas, creación inactiva/pendiente de profesores, obligatoriedad del consentimiento RGPD, bloqueo de login a cuentas no aprobadas, visualización de especialidad y aprobación por el admin, y habilitación del acceso tras la aprobación.

## 2026-10-15 — fix: Corrección de contraste en títulos de cabecera ("Notificaciones" y "Mis Prácticas y Horas")

- **Problema de visualización**:
  - En `notificaciones/index.blade.php` y `practicas/mis-practicas.blade.php` (así como en las vistas de proyectos y perfil), el encabezado usaba clases heredadas de Breeze (`font-semibold text-xl text-gray-800 dark:text-gray-200`).
  - Como Tailwind operaba con el modo dark por defecto (`media`), en cualquier navegador o sistema operativo con preferencia de tema oscuro se activaba `dark:text-gray-200` (`#E5E7EB`, gris casi blanco). Dado que la barra de cabecera de la aplicación (`layouts/app.blade.php`) es blanca translúcida (`bg-white/90`), el texto se volvía prácticamente invisible por falta de contraste.
- **Solución implementada**:
  - `resources/views/notificaciones/index.blade.php`: actualizado el slot de header a `font-bold text-xl sm:text-2xl font-display text-slate-900 leading-tight` junto a su subtítulo explicativo, adaptando además la lista al diseño de tarjetas Stitch UI con badges de color por tipo de notificación.
  - `resources/views/practicas/mis-practicas.blade.php`: actualizado el título a "Mis Prácticas y Horas" con tipografía `text-slate-900`, subtítulo y rediseño completo de la tabla mediante `stitch-table-container`, badges Stitch UI y tarjetas responsivas para móviles (`sm:hidden`).
  - `resources/views/profile/edit.blade.php`, `proyectos/create.blade.php`, `proyectos/edit.blade.php` y `proyectos/show.blade.php`: corregidos los encabezados para usar la tipografía de alto contraste `text-slate-900`.
  - `tailwind.config.js`: configurado `darkMode: 'class'` para prevenir que los estilos oscuros del sistema operativo filtren colores claros sobre componentes de fondo blanco sin una clase `.dark` explícita.

## 2026-10-15 — fix: Corrección de error 500 en subida WebP y verificación de límites de peso (2MB / 500KB)

- **Causa raíz del error 500 al subir WebP**:
  - `ProfileController` invocaba directamente a `imagecreatefromwebp()`. En el contenedor PHP (`Dockerfile`), la extensión GD se compilaba sin soporte WebP (`libwebp-dev` no estaba instalado y faltaba `--with-webp` en `docker-php-ext-configure gd`). Al no existir la función, PHP lanzaba un error fatal no capturado: `Call to undefined function App\Http\Controllers\imagecreatefromwebp()`, provocando HTTP 500.
  - Además, si el archivo subido tenía una extensión incorrecta o GD fallaba, el error no era capturado y reventaba la petición.
- **Causa raíz del fallo con imágenes > 500KB**:
  1. **Nginx `client_max_body_size`**: Por defecto en Nginx es `1m` (1 MB). Cualquier imagen entre 1MB y 2MB (o subida combinada avatar + CV) era rechazada de inmediato por Nginx con `413 Request Entity Too Large`.
  2. **Consumo de memoria en GD al descomprimir**: Imágenes de 500KB-800KB capturadas con cámaras o móviles suelen tener altas resoluciones (12 a 24 megapíxeles, ej. 4000x3000px). GD descomprime el mapa de bits completo en memoria ($4000 \times 3000 \times 4 \approx 48\text{ MB}$), superando el `memory_limit` de PHP si no está configurado holgadamente, generando fatales `Allowed memory size exhausted`.
- **Soluciones implementadas**:
  - `ProfileController` y `ProyectoController`:
    - Funciones GD blindadas con `function_exists()` (`imagecreatefromwebp`, `imagewebp`, `imagecreatefromjpeg`, `imagecreatefrompng`).
    - Detección precisa por tipo MIME (`getimagesize()`) además de la extensión.
    - Incremento temporal de memoria durante el procesamiento (`ini_set('memory_limit', '256M')`).
    - Bloque `try { ... } catch (\Throwable $e)`: si la imagen no puede procesarse por GD (formato sin soporte en el servidor, límite de memoria o archivo no estándar), se guarda el archivo original de forma segura con `store()` sin lanzar jamás un error 500.
  - `ProfileUpdateRequest`:
    - Mensajes de validación en español claros y personalizados para límites de peso y formatos permitidos.
  - `resources/views/profile/partials/update-profile-information-form.blade.php`:
    - Validación reactiva en cliente con Alpine.js que avisa instantáneamente al usuario si selecciona un archivo que sobrepasa 2 MB (avatar) o 5 MB (CV).
  - Configuración del servidor y Docker:
    - `Dockerfile`: añadido `libwebp-dev`, `--with-webp` en la compilación de GD, y copia de `docker/php/uploads.ini`.
    - `docker/php/uploads.ini`: `upload_max_filesize = 20M`, `post_max_size = 25M`, `memory_limit = 256M`.
    - `docker-compose.yml`: montado `uploads.ini` en `/usr/local/etc/php/conf.d/uploads.ini`.
    - `docker/nginx/default.conf`: añadido `client_max_body_size 25M;`.
  - Tests en `tests/Feature/ProfileTest.php`:
    - Tests de subida de avatar WebP, rechazo de archivos superiores a 2MB y aceptación de archivos de 1.5MB (> 500KB).

## 2026-10-14 — reg-001: Auto-registro de alumnos con selección de grupos y aprobación de administrador

- **Migración de base de datos**:
  - `database/migrations/2026_10_14_230000_add_registration_status_to_users_table.php`: añade `registration_status` (`approved`, `pending`, `rejected`), `registration_approved_at` y `registration_rejected_at` a la tabla `users`.
  - Migración aplicada con éxito tanto en SQLite como en MySQL de producción (`docker exec ieshlanz-app php artisan migrate`).
- **Modelo User**:
  - Constantes `STATUS_APPROVED`, `STATUS_PENDING`, `STATUS_REJECTED`.
  - Atributos `$fillable` y `$casts` actualizados.
  - Métodos helper: `isPendingApproval()`, `isRegistrationApproved()`, `isRegistrationRejected()`.
- **Formulario y Controlador de Registro**:
  - `RegisteredUserController::create`: pasa los grupos activos con su línea, ciclo y curso a la vista.
  - `resources/views/auth/register.blade.php`: selector interactivo vía Alpine.js que despliega los grupos activos en un listado de selección múltiple al seleccionar rol Alumno/a; aviso explícito de estado de espera y aceptación obligatoria de RGPD y Aviso de Privacidad.
  - `RegisteredUserController::store`: al registrarse un alumno, se crea inactivo (`is_active=false`) en estado `pending`, se crea el modelo `Alumno` y se asocian los grupos seleccionados vinculados al curso académico. No se inicia sesión y se redirige a `/login` con mensaje informativo.
- **Control de Acceso y Middleware**:
  - `LoginRequest`: si un usuario con credenciales válidas está en espera (`pending`), se detiene el acceso y se muestra el mensaje de que su cuenta está pendiente de aprobación por el administrador.
  - `CheckUserActive`: middleware refuerza la expulsión y redirección a login si una cuenta en espera o desactivada intentase acceder.
- **Panel de Administración**:
  - `Admin\UserController`: nuevos métodos `approve(User $user)` y `reject(User $user)`.
  - Rutas `admin.users.approve` y `admin.users.reject` en `routes/web.php`.
  - Vista `admin/users/index.blade.php`: badge de estado "En espera", visualización de los grupos solicitados por el alumno, botones para aceptar/habilitar o rechazar la solicitud, y filtros por estado de registro (`pending`, `approved`, `rejected`).
  - `Admin\DashboardController` y `resources/views/admin/dashboard.blade.php`: banner destacado y contador de solicitudes de alumnos pendientes de aprobación.
  - `resources/views/layouts/app.blade.php`: badge con número de alumnos pendientes junto a "Gestión Usuarios" en el menú lateral.
- **Tests y Validación**:
  - `tests/Feature/AlumnoRegistrationApprovalTest.php`: 7 tests completos que cubren visualización de grupos, registro en estado de espera, bloqueo de login previo, aprobación por admin, login posterior del alumno habilitado, rechazo por admin y control de permisos de acceso.
  - `tests/Feature/Auth/RegistrationTest.php`: actualizado y pasando.
  - Arneses de validación de SDD (`./scripts/init.sh`) completados sin errores.

## 2026-09-02 — ui-stitch-001: Rediseño visual Stitch UI y Responsividad Global

- **Sistema de diseño Stitch UI**:
  - Definición de estilos de utilidad globales en `resources/css/app.css` (`.stitch-card`, `.stitch-badge-*`, `.stitch-btn-*`, `.stitch-input`, `.stitch-table-container`, `.stitch-table`, `.stitch-toolbar`, `.stitch-glass`).
  - Aplicación estricta de las directrices del SPEC: fondo claro (`bg-slate-50`), tarjetas elevadas blancas (`bg-white`), tipografías *Outfit* e *Inter*.
- **Layouts base**:
  - `layouts/app.blade.php`: sidebar institucional oscuro (`#0F172A`), navegación dividida por roles, drawer responsivo para dispositivos móviles, top header flotante con blur.
  - `layouts/guest.blade.php` y `layouts/public.blade.php`: acabados de marca pulidos, hero branding, pie de página institucional.
- **Rediseño completo de módulos principales**:
  - `dashboard.blade.php`: Banner institucional con accesos rápidos, métricas en grid responsivo y notificaciones en vivo.
  - `alumnos/index.blade.php` y `alumnos/create.blade.php`: Toolbar de búsqueda/filtros, tabla de alumnos con avatares, badges de ciclos/grupos y tarjetas responsivas para pantallas móviles (`sm:hidden`).
  - `profesores/index.blade.php`: Tabla de profesorado con insignias de tutoría/coordinador dual y filtros por rol.
  - `empresas/index.blade.php`: Listado de empresas colaboradoras con badges de ofertas y prácticas.
  - `ofertas/index.blade.php`: Grid responsivo de tarjetas de convocatorias FCT con estados y especialidades.
  - `practicas/index.blade.php`: Seguimiento de prácticas de alumnado con horas acumuladas.
  - `proyectos/index.blade.php`: Galería de proyectos con tarjetas multimedia, repositorio, enlace a demo online e insignias de destacados.
- **Validación**:
  - Todos los arneses de validación de SDD (`scripts/init.sh`) en verde.

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

## 2026-10-21 — Alumnado: empresa 1º y 2º, Prácticas por Grupo enriquecidas y Fix persistencia asignación ofertas

### Listado de Alumnado (alumnos/index)
- Nuevo accessor `empresa_segundo` en `Alumno`: obtiene la empresa asignada para el alumno en 2º curso (priorizando práctica registrada en el curso actual o más reciente, o solicitud de oferta de prácticas con estado `aceptado`).
- `AlumnoController::index`: eager loading de `practicas.empresa` y `solicitudesPracticas.oferta.empresa` para evitar consultas N+1.
- `resources/views/alumnos/index.blade.php`: nuevas columnas en tabla de escritorio ("Prácticas 1º" y "Prácticas 2º") con nombre de empresa y horas de 1º, y badge de empresa asignada en 2º. También integrado en tarjetas móviles.

### Prácticas por Grupo (practicas/grupos/show)
- Sección 1 (Alumnos sin práctica) y Sección 3 (Alumnos con práctica):
  - Avatar / foto a la izquierda de cada alumno (con inicial de fallback si no tiene avatar).
  - Nombre del alumno completamente enlazable a su perfil y expediente (`alumnos.show`).
  - Al lado/debajo del nombre: empresa de prácticas de 1º y horas realizadas (`empresa_practicas_primero` y `horas_practicas_primero`).
- Sección 2 (Plazas disponibles):
  - Empresa y especialidad enlazables directamente al detalle de la oferta (`ofertas.show`).
  - Nueva columna de acciones con botón "Asignar alumnos" (con preselección del grupo actual) y botón "Ver oferta".

### Formulario de Asignación de Alumnado a Ofertas (Fix de persistencia)
- `OfertaPracticaController::enviarAAlumnos`: anteriormente solo enviaba notificaciones sin guardar la asignación. Ahora persiste la asignación como `SolicitudPractica` con estado `aceptado`, retira solicitudes de alumnos desmarcados, envía notificaciones de empresa asignada y oferta, y redirige a la vista previa o detalle de oferta.
- `OfertaPracticaController::enviarForm`: preselecciona los alumnos previamente asignados en lugar de seleccionar todos por defecto; si se accede desde un grupo (`grupo_id`), lo asocia si no estaba vinculado.
- `resources/views/ofertas/enviar.blade.php`: título descriptivo "Asignar alumnado a oferta de prácticas", inicialización con alumnos asignados, botón "Guardar asignación", soporte para cancelar y retornar al grupo de origen.
- `resources/views/ofertas/show.blade.php`: corrección de `$solicitud->alumno->user->name` y acciones para gestionar solicitudes.
