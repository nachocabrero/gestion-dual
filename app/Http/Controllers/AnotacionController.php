<?php

namespace App\Http\Controllers;

use App\Models\Anotacion;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de anotaciones/tutorías.
 * Acceso: Tutor del grupo, Coordinador Dual, Admin.
 */
class AnotacionController extends Controller
{
    /**
     * Listar anotaciones (según rol).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Solo roles autorizados
        abort_unless($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL, User::ROLE_PROFESOR]), 403);

        $query = Anotacion::with(['alumno.user', 'alumno.grupos', 'profesor.user']);

        if ($user->hasRole(User::ROLE_PROFESOR)) {
            // Profesor ve sus anotaciones + las de alumnos de los grupos que imparte
            $query->visiblesPara($user->profesor?->id);
        } elseif ($user->hasRole(User::ROLE_COORDINADOR_DUAL)) {
            // Coordinador ve todas
        } else {
            // Admin ve todas
        }

        if ($request->filled('alumno')) {
            $query->whereHas('alumno.user', fn($q) => $q->where('name', 'like', '%' . $request->alumno . '%'));
        }

        $anotaciones = $query->paginate(30);

        return view('anotaciones.index', compact('anotaciones'));
    }

    /**
     * Formulario crear anotación.
     */
    public function create(Request $request): View
    {
        $alumnoId = $request->query('alumno');

        // Solo coordinadores y admin pueden crear
        abort_unless(auth()->user()->hasAnyRole([User::ROLE_COORDINADOR_DUAL, User::ROLE_ADMIN]), 403);

        $alumnos = Alumno::with('user', 'grupos')->get();

        return view('anotaciones.create', compact('alumnos', 'alumnoId'));
    }

    /**
     * Guardar anotación.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'contenido' => ['required', 'string', 'max:2000'],
            'puesto' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $profesor = auth()->user()->profesor;

        Anotacion::create([
            'alumno_id' => $validated['alumno_id'],
            'profesor_id' => $profesor ? $profesor->id : null,
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'puesto' => $validated['puesto'] ?? null,
        ]);

        return redirect()->route('anotaciones.index')
            ->with('success', 'Anotación creada correctamente.');
    }

    /**
     * Formulario editar.
     */
    public function edit(Anotacion $anotacion): View
    {
        return view('anotaciones.edit', compact('anotacion'));
    }

    /**
     * Actualizar anotación.
     */
    public function update(Request $request, Anotacion $anotacion): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'contenido' => ['required', 'string', 'max:2000'],
            'puesto' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $anotacion->update($validated);

        return redirect()->route('anotaciones.show', $anotacion->alumno_id)
            ->with('success', 'Anotación actualizada.');
    }

    /**
     * Eliminar anotación.
     */
    public function destroy(Anotacion $anotacion): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $anotacion->delete();
        return back()->with('success', 'Anotación eliminada.');
    }

    /**
     * Ver anotaciones de un alumno.
     */
    public function show(int $alumnoId): View
    {
        $alumno = Alumno::with('user', 'grupos')->findOrFail($alumnoId);
        $user = auth()->user();

        // Verificar permisos
        if ($user->hasRole(User::ROLE_PROFESOR)) {
            // Solo si es tutor de alguno de los grupos del alumno
            $alumnoGrupos = $alumno->grupos;
            $esTutor = $alumnoGrupos->contains(function ($grupo) use ($user) {
                return $grupo->tutor_id === $user->id;
            });
            abort_unless($esTutor, 403);
        } elseif (!$user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
            abort(403);
        }

        $anotaciones = Anotacion::paraAlumno($alumnoId)->get();

        return view('anotaciones.show', compact('alumno', 'anotaciones'));
    }
}