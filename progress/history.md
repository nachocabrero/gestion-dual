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
