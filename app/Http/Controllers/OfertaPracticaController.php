<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\OfertaPractica;
use App\Models\SolicitudPractica;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

/**
 * Controlador de ofertas y solicitudes de prácticas.
 */
class OfertaPracticaController extends Controller
{
    public function __construct(protected NotificacionService $notificacionService) {}
    /**
     * Listar ofertas (según rol).
     */
    public function index(Request $request): View
    {
        $query = OfertaPractica::with(['empresa', 'creador', 'grupos']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('especialidad_requerida', 'like', "%{$search}%");
            });
        }

        if ($request->filled('especialidad')) {
            $query->porEspecialidad($request->especialidad);
        }

        if (auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO)) {
            // Alumno ve ofertas a las que se ha postulado o que están activas
            $alumno = auth()->user()->alumno;
            if ($alumno) {
                $query->where(function ($q) use ($alumno) {
                    $q->where('estado', 'activa')
                      ->orWhereHas('solicitudes', fn($sq) => $sq->where('alumno_id', $alumno->id));
                });
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50])) {
            $perPage = 15;
        }

        $ofertas = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        $especialidades = OfertaPractica::distinct()->pluck('especialidad_requerida')->toArray();

        return view('ofertas.index', compact('ofertas', 'especialidades'));
    }

    /**
     * Formulario crear oferta.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_EMPRESA, \App\Models\User::ROLE_ADMIN]), 403);

        $empresas = Empresa::active()->get();
        $grupos = $this->gruposCursoActual();

        return view('ofertas.create', compact('empresas', 'grupos'));
    }

    /**
     * Guardar oferta.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_EMPRESA, \App\Models\User::ROLE_ADMIN]), 403);

        $validated = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'especialidad_requerida' => ['required', 'string', 'max:100'],
            'num_alumnos' => ['required', 'integer', 'min:1', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'grupo_ids' => ['nullable', 'array'],
            'grupo_ids.*' => ['integer', 'exists:grupos,id'],
            'estado' => ['nullable', 'in:pendiente,activa,cerrada'],
        ]);

        $validated['creador_id'] = auth()->id();
        $validated['creador_type'] = User::class;
        $validated['estado'] = $validated['estado'] ?? 'pendiente';

        $oferta = OfertaPractica::create($validated);

        if (! empty($request->grupo_ids)) {
            $oferta->grupos()->sync($request->grupo_ids);
        }

        return redirect()->route('ofertas.show', $oferta)
            ->with('success', 'Oferta creada correctamente.');
    }

    /**
     * Ver oferta.
     */
    public function show(OfertaPractica $oferta): View
    {
        $oferta->load(['empresa', 'creador', 'solicitudes.alumno.user', 'solicitudes.alumno.practicas', 'grupos', 'practicas']);

        $thisCanEdit = $this->canEditOferta($oferta);
        $thisCanEnviar = $this->canEnviarOferta($oferta);
        $thisCanDescargarCvs = $this->canDescargarCvs($oferta);

        $totalCvsDisponibles = $oferta->solicitudes->filter(function ($solicitud) {
            $user = $solicitud->alumno?->user;
            return $user && $user->cv_url && Storage::disk('public')->exists($user->cv_url);
        })->count();

        return view('ofertas.show', compact('oferta', 'thisCanEdit', 'thisCanEnviar', 'thisCanDescargarCvs', 'totalCvsDisponibles'));
    }

    /**
     * Formulario editar.
     */
    public function edit(OfertaPractica $oferta): View
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $empresas = Empresa::active()->get();
        $grupos = $this->gruposCursoActual();

        return view('ofertas.edit', compact('oferta', 'empresas', 'grupos'));
    }

    /**
     * Actualizar oferta.
     */
    public function update(Request $request, OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $validated = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'especialidad_requerida' => ['required', 'string', 'max:100'],
            'num_alumnos' => ['required', 'integer', 'min:1', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'estado' => ['required', 'in:pendiente,activa,cerrada'],
            'grupo_ids' => ['nullable', 'array'],
            'grupo_ids.*' => ['integer', 'exists:grupos,id'],
        ]);

        $oferta->update($validated);

        if ($request->has('grupo_ids')) {
            $oferta->grupos()->sync($request->grupo_ids);
        }

        return redirect()->route('ofertas.show', $oferta)
            ->with('success', 'Oferta actualizada.');
    }

    /**
     * Eliminar oferta.
     */
    public function destroy(OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $oferta->delete();
        return redirect()->route('ofertas.index')
            ->with('success', 'Oferta eliminada.');
    }

    /**
     * Formulario para asignar y enviar la oferta a alumnos:
     * muestra los alumnos de los grupos a los que está dirigida la oferta,
     * preseleccionando aquellos que ya tienen plaza asignada (solicitud aceptada).
     */
    public function enviarForm(Request $request, OfertaPractica $oferta): View
    {
        abort_unless($this->canEnviarOferta($oferta), 403);

        $oferta->load(['grupos', 'solicitudes']);
        $cursoActual = \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        // Si se pasa grupo_id y no está vinculado a la oferta, vincularlo automáticamente
        if ($request->filled('grupo_id')) {
            $grupoId = (int) $request->grupo_id;
            if (!$oferta->grupos->contains('id', $grupoId)) {
                $oferta->grupos()->attach($grupoId);
                $oferta->load('grupos');
            }
        }

        // Si la oferta no tiene grupos asignados, la lista de grupos debe ser vacía
        $grupos = $oferta->grupos->isEmpty()
            ? collect()
            : Grupo::whereIn('id', $oferta->grupos->pluck('id'))
                ->orderBy('nombre')
                ->with('alumnos.user')
                ->get()
                ->map(fn($g) => [
                    'id' => $g->id,
                    'nombre' => $g->nombre ?: ('Grupo ' . $g->numero),
                    'alumnos' => $g->alumnos
                        ->filter(fn($a) => $a->user && $a->user->hasRole(\App\Models\User::ROLE_ALUMNO))
                        ->map(fn($a) => [
                            'id' => $a->id,
                            'nombre' => $a->user->name,
                            'email' => $a->user->email,
                        ])
                        ->values(),
                ])
                ->values();

        // Alumnos ya asignados (solicitud con estado 'aceptado')
        $alumnosAsignadosIds = $oferta->solicitudes
            ->where('estado', 'aceptado')
            ->pluck('alumno_id')
            ->values()
            ->toArray();

        $grupoSeleccionadoId = $request->input('grupo_id');

        return view('ofertas.enviar', compact('oferta', 'grupos', 'alumnosAsignadosIds', 'grupoSeleccionadoId'));
    }

    /**
     * Guardar la asignación de la oferta a los alumnos seleccionados.
     * Persiste la asignación como SolicitudPractica aceptada, activa la oferta si estaba pendiente
     * y notifica a cada alumno asignado.
     */
    public function enviarAAlumnos(Request $request, OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEnviarOferta($oferta), 403);

        $validated = $request->validate([
            'alumno_ids' => ['required', 'array', 'min:1'],
            'alumno_ids.*' => ['integer', 'exists:alumnos,id'],
            'grupo_id' => ['nullable', 'integer'],
            'redirect_to' => ['nullable', 'string'],
        ], [
            'alumno_ids.required' => 'Selecciona al menos un alumno para asignar a la oferta.',
            'alumno_ids.min' => 'Selecciona al menos un alumno para asignar a la oferta.',
        ]);

        $selectedIds = $validated['alumno_ids'];

        $cursoActual = \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        // Alumnos de los grupos destinatarios
        $alumnosGruposIds = Alumno::whereHas('grupos', function($q) use ($oferta, $cursoActual) {
            $q->whereIn('grupos.id', $oferta->grupos->pluck('id'));
            if ($cursoActual) {
                $q->where(function($sub) use ($cursoActual) {
                    $sub->where('alumno_grupo.curso_academico_id', $cursoActual->id)
                        ->orWhereNull('alumno_grupo.curso_academico_id');
                });
            }
        })->pluck('id')->toArray();

        // Retirar a los alumnos que antes estaban asignados pero han sido desmarcados
        if (!empty($alumnosGruposIds)) {
            SolicitudPractica::where('oferta_id', $oferta->id)
                ->whereIn('alumno_id', $alumnosGruposIds)
                ->whereNotIn('alumno_id', $selectedIds)
                ->where('estado', 'aceptado')
                ->update(['estado' => 'retirado']);
        }

        $alumnos = Alumno::whereIn('id', $selectedIds)
            ->with('user')
            ->get()
            ->filter(fn($a) => $a->user && $a->user->hasRole(\App\Models\User::ROLE_ALUMNO));

        if ($alumnos->isEmpty()) {
            return back()->withErrors(['alumno_ids' => 'Selecciona al menos un alumno válido.']);
        }

        foreach ($alumnos as $alumno) {
            SolicitudPractica::updateOrCreate(
                [
                    'oferta_id' => $oferta->id,
                    'alumno_id' => $alumno->id,
                ],
                [
                    'estado' => 'aceptado',
                    'motivo_rechazo' => null,
                ]
            );

            // Notificaciones de oferta y asignación de empresa
            $this->notificacionService->ofertaEnviada($alumno, $oferta);
            if ($oferta->empresa) {
                $this->notificacionService->empresaAsignada($alumno->id, $oferta->empresa->nombre);
            }
        }

        if ($oferta->estado === 'pendiente') {
            $oferta->update(['estado' => 'activa']);
        }

        $msg = "Se ha asignado correctamente a {$alumnos->count()} alumno(s) a la oferta.";

        if ($request->filled('grupo_id')) {
            return redirect()->route('practicas.grupos.show', $request->grupo_id)->with('success', $msg);
        }

        if ($request->filled('redirect_to') && str_starts_with($request->redirect_to, '/')) {
            return redirect($request->redirect_to)->with('success', $msg);
        }

        return redirect()->route('ofertas.show', $oferta)->with('success', $msg);
    }

    /**
     * Postularse a una oferta (alumno).
     */
    public function postularse(Request $request, OfertaPractica $oferta): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        $alumno = auth()->user()->alumno;
        abort_unless($alumno, 404);

        if ($oferta->estado !== 'activa') {
            return back()->withErrors(['error' => 'Esta oferta no está activa.']);
        }

        // Verificar si ya tiene una solicitud pendiente/aceptada
        $existing = SolicitudPractica::where('oferta_id', $oferta->id)
            ->where('alumno_id', $alumno->id)
            ->whereIn('estado', ['pendiente', 'aceptado'])
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'Ya tienes una solicitud para esta oferta.']);
        }

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud enviada correctamente.');
    }

    /**
     * Retirar solicitud (alumno).
     */
    public function retirar(Request $request, SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        if ($solicitud->alumno_id !== auth()->user()->alumno->id) {
            abort(403);
        }

        if ($solicitud->estado !== 'pendiente' && $solicitud->estado !== 'aceptado') {
            return back()->withErrors(['error' => 'No puedes retirar esta solicitud.']);
        }

        $solicitud->update(['estado' => 'retirado']);
        return back()->with('success', 'Solicitud retirada.');
    }

    /**
     * Aceptar solicitud (profesor/coordinador).
     */
    public function aceptar(SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_ADMIN]), 403);

        if ($solicitud->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La solicitud no está pendiente.']);
        }

        $solicitud->update(['estado' => 'aceptado']);

        // Notificar al alumno que se le asignó la empresa
        $alumno = $solicitud->alumno;
        $empresa = $solicitud->oferta->empresa;
        if ($alumno && $empresa) {
            $this->notificacionService->empresaAsignada(
                $alumno->id,
                $empresa->nombre
            );
        }

        return back()->with('success', 'Solicitud aceptada.');
    }

    /**
     * Rechazar solicitud (profesor/coordinador).
     */
    public function rechazar(Request $request, SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_ADMIN]), 403);

        if ($solicitud->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La solicitud no está pendiente.']);
        }

        $solicitud->update([
            'estado' => 'rechazado',
            'motivo_rechazo' => $request->input('motivo_rechazo'),
        ]);
        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * Ver solicitudes de una oferta (profesor/empresa).
     */
    public function solicitudes(OfertaPractica $oferta): View
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $solicitudes = $oferta->solicitudes()->with('alumno.user')->get();

        return view('ofertas.solicitudes', compact('oferta', 'solicitudes'));
    }

    /**
     * Ver ofertas de un alumno.
     */
    public function misOfertas(Request $request): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        $alumno = auth()->user()->alumno;
        abort_unless($alumno, 404);

        $solicitudes = $alumno->solicitudesPracticas()
            ->with('oferta.empresa')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('ofertas.mis-ofertas', compact('solicitudes'));
    }

    /**
     * Grupos activos del curso académico actual.
     */
    private function gruposCursoActual()
    {
        $cursoActual = \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        return Grupo::active()
            ->when($cursoActual, fn($q) => $q->where('curso_academico_id', $cursoActual->id))
            ->with('linea')
            ->orderBy('nombre')
            ->get();
    }

    private function canEditOferta(OfertaPractica $oferta): bool
    {
        $user = auth()->user();

        // El creador puede editar
        if ($oferta->creador_id === $user->id && $oferta->creador_type === User::class) {
            return true;
        }

        // Admin puede editar todo
        if ($user->hasRole(\App\Models\User::ROLE_ADMIN)) {
            return true;
        }

        return false;
    }

    private function canEnviarOferta(OfertaPractica $oferta): bool
    {
        return $this->canEditOferta($oferta)
            || auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_COORDINADOR_DUAL]);
    }

    /**
     * Comprobar si el usuario autenticado tiene permiso para descargar CVs de la oferta.
     */
    private function canDescargarCvs(OfertaPractica $oferta): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Administradores, Profesores y Coordinadores Duales
        if ($user->hasRole(User::ROLE_ADMIN) || $user->hasAnyRole([User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL])) {
            return true;
        }

        // Creador de la oferta
        if ($oferta->creador_id === $user->id && $oferta->creador_type === User::class) {
            return true;
        }

        // Representante de Empresa
        if ($user->hasRole(User::ROLE_EMPRESA)) {
            return true;
        }

        return false;
    }

    /**
     * Descargar CV individual de un alumno en una oferta.
     */
    public function descargarCvAlumno(OfertaPractica $oferta, Alumno $alumno)
    {
        abort_unless($this->canDescargarCvs($oferta), 403);

        $solicitud = $oferta->solicitudes()->where('alumno_id', $alumno->id)->first();
        abort_unless($solicitud, 404);

        $user = $alumno->user;
        if (! $user || ! $user->cv_url || ! Storage::disk('public')->exists($user->cv_url)) {
            return back()->with('warning', 'El alumno no tiene ningún Currículum Vitae subido.');
        }

        $extension = pathinfo($user->cv_url, PATHINFO_EXTENSION) ?: 'pdf';
        $nombreLimpio = Str::slug($user->name ?: 'alumno_' . $alumno->id, '_');
        $fileName = "CV_{$nombreLimpio}.{$extension}";

        return Storage::disk('public')->download($user->cv_url, $fileName);
    }

    /**
     * Regenerar el token único de acceso compartido para la empresa.
     */
    public function regenerarToken(OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEnviarOferta($oferta), 403);

        $oferta->regenerarToken();

        return back()->with('success', 'Se ha generado un nuevo enlace seguro para la empresa. El enlace anterior ha quedado revocado.');
    }

    /**
     * Vista pública de oferta compartida con la empresa mediante token seguro.
     */
    public function compartida(string $token): View
    {
        $oferta = OfertaPractica::where('token', $token)
            ->with(['empresa', 'creador', 'solicitudes.alumno.user', 'grupos.linea.ciclo'])
            ->firstOrFail();

        $totalCvsDisponibles = $oferta->solicitudes->filter(function ($solicitud) {
            $user = $solicitud->alumno?->user;
            return $user && $user->cv_url && Storage::disk('public')->exists($user->cv_url);
        })->count();

        return view('ofertas.compartida', compact('oferta', 'totalCvsDisponibles'));
    }

    /**
     * Descarga de CV de un alumno desde el enlace compartido con token para la empresa.
     */
    public function descargarCvAlumnoCompartida(string $token, Alumno $alumno)
    {
        $oferta = OfertaPractica::where('token', $token)->firstOrFail();

        $solicitud = $oferta->solicitudes()->where('alumno_id', $alumno->id)->first();
        abort_unless($solicitud, 404);

        $user = $alumno->user;
        if (! $user || ! $user->cv_url || ! Storage::disk('public')->exists($user->cv_url)) {
            return back()->with('warning', 'El alumno no tiene ningún Currículum Vitae subido.');
        }

        $extension = pathinfo($user->cv_url, PATHINFO_EXTENSION) ?: 'pdf';
        $nombreLimpio = Str::slug($user->name ?: 'alumno_' . $alumno->id, '_');
        $fileName = "CV_{$nombreLimpio}.{$extension}";

        return Storage::disk('public')->download($user->cv_url, $fileName);
    }

    /**
     * Descarga de todos los CVs en ZIP desde el enlace compartido con token para la empresa.
     */
    public function descargarCvsZipCompartida(string $token)
    {
        $oferta = OfertaPractica::where('token', $token)
            ->with(['solicitudes.alumno.user', 'empresa'])
            ->firstOrFail();

        return $this->generarZipCvs($oferta);
    }

    /**
     * Descargar todos los CVs de los alumnos de la oferta en un archivo ZIP (para usuarios autenticados).
     */
    public function descargarCvsZip(OfertaPractica $oferta)
    {
        abort_unless($this->canDescargarCvs($oferta), 403);

        $oferta->load(['solicitudes.alumno.user', 'empresa']);

        return $this->generarZipCvs($oferta);
    }

    /**
     * Helper privado para generar y servir el archivo ZIP con los CVs de la oferta.
     */
    private function generarZipCvs(OfertaPractica $oferta)
    {
        $archivosCv = [];
        foreach ($oferta->solicitudes as $solicitud) {
            $alumno = $solicitud->alumno;
            $user = $alumno?->user;
            if ($user && $user->cv_url && Storage::disk('public')->exists($user->cv_url)) {
                $realPath = Storage::disk('public')->path($user->cv_url);
                $extension = pathinfo($user->cv_url, PATHINFO_EXTENSION) ?: 'pdf';
                $nombreLimpio = Str::slug($user->name ?: 'alumno_' . $alumno->id, '_');
                $baseFileName = "CV_{$nombreLimpio}.{$extension}";

                // Evitar colisiones de nombres de archivo
                $fileName = $baseFileName;
                $counter = 1;
                while (isset($archivosCv[$fileName])) {
                    $fileName = "CV_{$nombreLimpio}_{$counter}.{$extension}";
                    $counter++;
                }

                $archivosCv[$fileName] = $realPath;
            }
        }

        if (empty($archivosCv)) {
            return back()->with('warning', 'Ninguno de los alumnos asignados o postulados a esta oferta tiene un CV disponible para descargar.');
        }

        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'cvs_oferta_');
        $empresaNombre = Str::slug($oferta->empresa?->nombre ?: 'empresa', '_');
        $especialidad = Str::slug($oferta->especialidad_requerida ?: 'oferta', '_');
        $zipFileName = "CVs_{$empresaNombre}_{$especialidad}.zip";

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['error' => 'No se pudo generar el archivo comprimido ZIP.']);
        }

        foreach ($archivosCv as $localName => $filePath) {
            $zip->addFile($filePath, $localName);
        }

        $zip->close();

        return response()->download($tempFile, $zipFileName)->deleteFileAfterSend(true);
    }
}