<?php

namespace App\Http\Controllers\Admin\Estructura;

use App\Models\CursoAcademico;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrupoController
{
    /**
     * Grupos dentro de una línea.
     */
    public function index(Linea $linea): View
    {
        $grupos = $linea->grupos()->with('tutor', 'cursoAcademico')->withCount('alumnos')->get();

        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
        $gruposActuales = $grupos->where('curso_academico_id', $cursoActual?->id);
        $gruposAnteriores = $grupos->where('curso_academico_id', '!=', $cursoActual?->id);

        return view('admin.estructura.grupos.index', compact('linea', 'gruposActuales', 'gruposAnteriores', 'cursoActual'));
    }

    public function create(Linea $linea): View
    {
        $cursos = CursoAcademico::orderByDesc('id')->get();
        $tutores = Profesor::with('user')->get();

        return view('admin.estructura.grupos.create', compact('linea', 'cursos', 'tutores'));
    }

    /**
     * Formulario de nuevo grupo desde el nivel de ciclo (elige la línea del ciclo).
     */
    public function createPorCiclo(Ciclo $ciclo): View
    {
        $lineas = $ciclo->lineas()->active()->get();
        $cursos = CursoAcademico::orderByDesc('id')->get();
        $tutores = Profesor::with('user')->get();

        return view('admin.estructura.grupos.create', compact('ciclo', 'lineas', 'cursos', 'tutores'));
    }

    public function store(Request $request, Linea $linea): RedirectResponse
    {
        $validated = $request->validate([
            'numero' => ['required', 'integer', 'min:1'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'curso_academico_id' => ['nullable', 'exists:cursos_academicos,id'],
            'tutor_id' => ['nullable', 'exists:users,id'],
        ]);

        $linea->grupos()->create($validated);

        return redirect()->route('admin.estructura.grupos.index', $linea)
            ->with('success', 'Grupo creado correctamente.');
    }

    /**
     * Crea un grupo desde el nivel de ciclo eligiendo la línea.
     */
    public function storePorCiclo(Request $request, Ciclo $ciclo): RedirectResponse
    {
        $validated = $request->validate([
            'linea_id' => ['required', 'exists:lineas,id'],
            'numero' => ['required', 'integer', 'min:1'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'curso_academico_id' => ['nullable', 'exists:cursos_academicos,id'],
            'tutor_id' => ['nullable', 'exists:users,id'],
        ]);

        Linea::findOrFail($validated['linea_id'])->grupos()->create($validated);

        return redirect()->route('admin.estructura.grupos.index', $validated['linea_id'])
            ->with('success', 'Grupo creado correctamente.');
    }

    public function show(Grupo $grupo, Request $request): View
    {
        $grupo->load(['linea.ciclo.familia', 'tutor', 'cursoAcademico', 'alumnos.user']);

        $miembros = $grupo->alumnos->sortBy(fn($a) => $a->user->name);

        // Alumnos que aún no pertenecen al grupo, filtrables por nombre/email.
        $query = Alumno::with('user')->whereDoesntHave('grupos', fn($q) => $q->where('grupos.id', $grupo->id));
        if ($request->filled('buscar')) {
            $buscar = $request->string('buscar')->trim();
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $buscar . '%')
                ->orWhere('email', 'like', '%' . $buscar . '%'));
        }
        $candidatos = $query->orderBy('user_id')->limit(30)->get()->sortBy(fn($a) => $a->user->name);

        return view('admin.estructura.grupos.show', compact('grupo', 'miembros', 'candidatos'));
    }

    public function addAlumno(Request $request, Grupo $grupo): RedirectResponse
    {
        $validated = $request->validate([
            'alumno_id' => ['required', 'exists:alumnos,id'],
        ]);

        $alumno = Alumno::findOrFail($validated['alumno_id']);

        if (!$grupo->alumnos()->where('alumnos.id', $alumno->id)->exists()) {
            $grupo->alumnos()->attach($alumno->id, [
                'curso_academico_id' => $grupo->curso_academico_id,
            ]);
        }

        return back()->with('success', 'Alumno añadido al grupo.');
    }

    public function removeAlumno(Grupo $grupo, Alumno $alumno): RedirectResponse
    {
        $grupo->alumnos()->detach($alumno->id);

        return back()->with('success', 'Alumno eliminado del grupo.');
    }

    public function edit(Grupo $grupo): View
    {
        $lineas = Linea::active()->get();
        $cursos = CursoAcademico::orderByDesc('id')->get();
        $tutores = Profesor::with('user')->get();

        return view('admin.estructura.grupos.edit', compact('grupo', 'lineas', 'cursos', 'tutores'));
    }

    public function update(Request $request, Grupo $grupo): RedirectResponse
    {
        $validated = $request->validate([
            'linea_id' => ['required', 'exists:lineas,id'],
            'numero' => ['required', 'integer', 'min:1'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'curso_academico_id' => ['nullable', 'exists:cursos_academicos,id'],
            'tutor_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $grupo->update($validated);

        return redirect()->route('admin.estructura.grupos.show', $grupo)
            ->with('success', 'Grupo actualizado correctamente.');
    }

    public function destroy(Grupo $grupo): RedirectResponse
    {
        $grupo->delete();

        return redirect()->back()->with('success', 'Grupo eliminado.');
    }
}
