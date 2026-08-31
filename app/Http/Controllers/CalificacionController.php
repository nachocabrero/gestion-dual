<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de calificaciones.
 * Acceso: Admin, Coordinador Dual, Profesor (solo sus grupos).
 * Los alumnos NO pueden ver calificaciones.
 */
class CalificacionController extends Controller
{
    /**
     * Listar calificaciones (Admin/Coordinador ven todas, profesor solo sus grupos).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Alumnos no pueden ver calificaciones
        abort_unless($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL, User::ROLE_PROFESOR]), 403);

        $query = Calificacion::with(['alumno.user', 'alumno.grupos', 'asignatura']);

        // Filtros
        if ($request->filled('alumno')) {
            $query->whereHas('alumno.user', fn($q) => $q->where('name', 'like', '%' . $request->alumno . '%'));
        }
        if ($request->filled('evaluacion')) {
            $query->where('evaluacion', $request->evaluacion);
        }

        // Restricción por rol
        if ($user->hasRole(User::ROLE_PROFESOR)) {
            // Profesor solo ve sus grupos
            $gruposProfesor = Grupo::where('tutor_id', $user->id)->pluck('id');
            $query->whereHas('alumno.grupos', fn($q) => $q->whereIn('grupos.id', $gruposProfesor));
        }

        $calificaciones = $query->paginate(30);

        return view('calificaciones.index', compact('calificaciones'));
    }

    /**
     * Formulario para crear/editar calificación.
     */
    public function create(Request $request): View
    {
        $alumnoId = $request->query('alumno');
        $asignaturaId = $request->query('asignatura');
        $evaluacion = $request->query('evaluacion', 'primera');

        $alumnos = Alumno::with('user', 'grupos')->get();
        $asignaturas = Asignatura::all();

        return view('calificaciones.create', compact(
            'alumnos', 'asignaturas', 'alumnoId', 'asignaturaId', 'evaluacion'
        ));
    }

    /**
     * Guardar calificación.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'asignatura_id' => ['required', 'exists:asignaturas,id'],
            'evaluacion' => ['required', 'in:primera,segunda,tercera'],
            'nota' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        // Verificar que no exista ya
        $exists = Calificacion::where('alumno_id', $validated['alumno_id'])
            ->where('asignatura_id', $validated['asignatura_id'])
            ->where('evaluacion', $validated['evaluacion'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['_error' => 'Ya existe una calificación para este alumno en esta evaluación.']);
        }

        Calificacion::create($validated);

        return redirect()->route('calificaciones.index')
            ->with('success', 'Calificación creada correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Calificacion $calificacion): View
    {
        $alumnos = Alumno::with('user', 'grupo')->get();
        $asignaturas = Asignatura::all();

        return view('calificaciones.edit', compact('calificacion', 'alumnos', 'asignaturas'));
    }

    /**
     * Actualizar calificación.
     */
    public function update(Request $request, Calificacion $calificacion): RedirectResponse
    {
        $validated = $request->validate([
            'nota' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $calificacion->update($validated);

        return redirect()->route('calificaciones.index')
            ->with('success', 'Calificación actualizada correctamente.');
    }

    /**
     * Eliminar calificación.
     */
    public function destroy(Calificacion $calificacion): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $calificacion->delete();
        return back()->with('success', 'Calificación eliminada.');
    }

    /**
     * Ver calificaciones de un alumno.
     */
    public function show(Alumno $alumno): View
    {
        $user = auth()->user();

        // Restricción: profesor solo ve sus grupos
        if ($user->hasRole(User::ROLE_PROFESOR)) {
            $tutorGrupos = Grupo::where('tutor_id', $user->id)->pluck('id');
            abort_unless($alumno->grupos->pluck('id')->intersect($tutorGrupos)->isNotEmpty(), 403);
        }

        // Admin y Coordinador ven todo
        if (!$user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL, User::ROLE_PROFESOR])) {
            abort(403);
        }

        $calificaciones = Calificacion::with(['asignatura'])
            ->where('alumno_id', $alumno->id)
            ->orderBy('evaluacion')
            ->get();

        // Calcular medias por evaluación
        $medias = [];
        foreach (Calificacion::evaluaciones() as $eval) {
            $media = Calificacion::where('alumno_id', $alumno->id)
                ->where('evaluacion', $eval)
                ->avg('nota');
            $medias[$eval] = $media ? number_format($media, 2) : '—';
        }

        return view('calificaciones.show', compact('alumno', 'calificaciones', 'medias'));
    }
}