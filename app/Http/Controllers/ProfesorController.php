<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\Sustitucion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de gestión de profesores.
 * Acceso: Admin, Coordinador Dual.
 */
class ProfesorController extends Controller
{
    /**
     * Listar todos los profesores.
     */
    public function index(Request $request): View
    {
        $query = Profesor::with(['user', 'gruposTutor', 'asignaturas']);

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%'));
        }

        if ($request->filled('es_tutor')) {
            $query->tutores();
        }

        if ($request->filled('es_coordinador')) {
            $query->coordinadores();
        }

        $profesores = $query->paginate(30);
        $profesores->getCollection()->sortBy('user.name');

        return view('profesores.index', compact('profesores'));
    }

    /**
     * Mostrar perfil de un profesor.
     */
    public function show(Profesor $profesor): View
    {
        $profesor->load(['user', 'gruposTutor', 'asignaturas', 'sustituciones']);

        return view('profesores.show', compact('profesor'));
    }

    /**
     * Formulario para crear profesor.
     */
    public function create(): View
    {
        $ciclos = Ciclo::active()->get();
        return view('profesores.create', compact('ciclos'));
    }

    /**
     * Guardar nuevo profesor.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'es_tutor' => ['nullable', 'boolean'],
            'es_coordinador_dual' => ['nullable', 'boolean'],
            'asignatura_ids' => ['nullable', 'array'],
            'asignatura_ids.*' => ['exists:asignaturas,id'],
            'grupo_ids' => ['nullable', 'array'],
            'grupo_ids.*' => ['exists:grupos,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            // Crear usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'roles' => [User::ROLE_PROFESOR],
                'is_active' => true,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]);

            // Crear profesor
            $profesor = Profesor::create([
                'user_id' => $user->id,
                'especialidad' => $validated['especialidad'] ?? null,
                'es_tutor' => $validated['es_tutor'] ?? false,
                'es_coordinador_dual' => $validated['es_coordinador_dual'] ?? false,
            ]);

            // Asignar asignaturas
            if (!empty($validated['asignatura_ids'])) {
                $profesor->asignaturas()->attach($validated['asignatura_ids']);
            }

            // Asignar a equipos educativos
            if (!empty($validated['grupo_ids'])) {
                $profesor->equiposEducativos()->attach($validated['grupo_ids']);
            }

            return redirect()->route('profesores.index')
                ->with('success', 'Profesor creado correctamente.');
        });
    }

    /**
     * Formulario para editar profesor.
     */
    public function edit(Profesor $profesor): View
    {
        $profesor->load(['asignaturas', 'equiposEducativos']);
        $ciclos = Ciclo::active()->get();
        $asignaturas = Asignatura::active()->get();
        $grupos = Grupo::active()->get();

        return view('profesores.edit', compact('profesor', 'ciclos', 'asignaturas', 'grupos'));
    }

    /**
     * Actualizar profesor.
     */
    public function update(Request $request, Profesor $profesor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $profesor->user_id],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'es_tutor' => ['nullable', 'boolean'],
            'es_coordinador_dual' => ['nullable', 'boolean'],
            'asignatura_ids' => ['nullable', 'array'],
            'asignatura_ids.*' => ['exists:asignaturas,id'],
            'grupo_ids' => ['nullable', 'array'],
            'grupo_ids.*' => ['exists:grupos,id'],
        ]);

        return DB::transaction(function () use ($validated, $profesor) {
            // Actualizar usuario
            $profesor->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // Actualizar profesor
            $profesor->update([
                'especialidad' => $validated['especialidad'] ?? null,
                'es_tutor' => $validated['es_tutor'] ?? false,
                'es_coordinador_dual' => $validated['es_coordinador_dual'] ?? false,
            ]);

            // Actualizar asignaturas
            $profesor->asignaturas()->detach();
            if (!empty($validated['asignatura_ids'])) {
                $profesor->asignaturas()->attach($validated['asignatura_ids']);
            }

            // Actualizar equipos educativos
            $profesor->equiposEducativos()->detach();
            if (!empty($validated['grupo_ids'])) {
                $profesor->equiposEducativos()->attach($validated['grupo_ids']);
            }

            return redirect()->route('profesores.show', $profesor)
                ->with('success', 'Profesor actualizado correctamente.');
        });
    }

    /**
     * Desactivar profesor.
     */
    public function deactivate(Profesor $profesor): RedirectResponse
    {
        $profesor->user->update(['is_active' => false]);
        return back()->with('success', 'Profesor desactivado.');
    }

    /**
     * Eliminar profesor.
     */
    public function destroy(Profesor $profesor): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $profesor->user->forceDelete();
        $profesor->forceDelete();

        return back()->with('success', 'Profesor eliminado definitivamente.');
    }

    /**
     * Crear sustitución.
     */
    public function storeSustitucion(Request $request, Profesor $profesor): RedirectResponse
    {
        $validated = $request->validate([
            'profesor_sustituto_id' => ['required', 'exists:profesores,id'],
            'asignatura_id' => ['nullable', 'exists:asignaturas,id'],
            'grupo_id' => ['nullable', 'exists:grupos,id'],
            'fecha_inicio' => ['required', 'date', 'after_or_equal:today'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        Sustitucion::create([
            'profesor_titular_id' => $profesor->id,
            'profesor_sustituto_id' => $validated['profesor_sustituto_id'],
            'asignatura_id' => $validated['asignatura_id'] ?? null,
            'grupo_id' => $validated['grupo_id'] ?? null,
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Sustitución creada correctamente.');
    }

    /**
     * Eliminar sustitución.
     */
    public function destroySustitucion(Sustitucion $sustitucion): RedirectResponse
    {
        $sustitucion->delete();
        return back()->with('success', 'Sustitución eliminada.');
    }
}