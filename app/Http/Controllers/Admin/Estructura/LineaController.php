<?php

namespace App\Http\Controllers\Admin\Estructura;

use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Linea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LineaController
{
    /**
     * Líneas (turnos) dentro de un ciclo.
     */
    public function index(Ciclo $ciclo): View
    {
        $lineas = $ciclo->lineas()->withCount('grupos')->orderBy('turno')->orderBy('nombre')->get();

        return view('admin.estructura.lineas.index', compact('ciclo', 'lineas'));
    }

    public function create(Ciclo $ciclo): View
    {
        return view('admin.estructura.lineas.create', compact('ciclo'));
    }

    public function store(Request $request, Ciclo $ciclo): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'turno' => ['required', 'in:manana,tarde'],
        ]);

        $ciclo->lineas()->create($validated);

        return redirect()->route('admin.estructura.lineas.index', $ciclo)
            ->with('success', 'Línea creada correctamente.');
    }

    public function show(Linea $linea): View
    {
        $linea->load(['ciclo.familia', 'grupos.cursoAcademico', 'grupos.tutor']);

        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
        $gruposActuales = $linea->grupos->where('curso_academico_id', $cursoActual?->id);
        $gruposAnteriores = $linea->grupos->where('curso_academico_id', '!=', $cursoActual?->id);

        return view('admin.estructura.lineas.show', compact('linea', 'gruposActuales', 'gruposAnteriores', 'cursoActual'));
    }

    public function edit(Linea $linea): View
    {
        $ciclos = Ciclo::active()->get();

        return view('admin.estructura.lineas.edit', compact('linea', 'ciclos'));
    }

    public function update(Request $request, Linea $linea): RedirectResponse
    {
        $validated = $request->validate([
            'ciclo_id' => ['required', 'exists:ciclos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'turno' => ['required', 'in:manana,tarde'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $linea->update($validated);

        return redirect()->route('admin.estructura.lineas.show', $linea)
            ->with('success', 'Línea actualizada correctamente.');
    }

    public function destroy(Linea $linea): RedirectResponse
    {
        if ($linea->grupos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: la línea tiene grupos asociados.');
        }

        $linea->delete();

        return redirect()->route('admin.estructura.familias.index')
            ->with('success', 'Línea eliminada.');
    }
}
