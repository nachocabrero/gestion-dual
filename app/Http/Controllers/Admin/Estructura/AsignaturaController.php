<?php

namespace App\Http\Controllers\Admin\Estructura;

use App\Models\Asignatura;
use App\Models\Ciclo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsignaturaController
{
    /**
     * Asignaturas dentro de un ciclo.
     */
    public function index(Ciclo $ciclo): View
    {
        $asignaturas = $ciclo->asignaturas()->withCount('profesores')->orderBy('nombre')->get();

        return view('admin.estructura.asignaturas.index', compact('ciclo', 'asignaturas'));
    }

    public function create(Ciclo $ciclo): View
    {
        return view('admin.estructura.asignaturas.create', compact('ciclo'));
    }

    public function store(Request $request, Ciclo $ciclo): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:255', 'unique:asignaturas,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'horas_semanales' => ['required', 'integer', 'min:1', 'max:40'],
            'es_practicas' => ['nullable', 'boolean'],
        ]);

        $ciclo->asignaturas()->create($validated);

        return redirect()->route('admin.estructura.asignaturas.index', $ciclo)
            ->with('success', 'Asignatura creada correctamente.');
    }

    public function edit(Asignatura $asignatura): View
    {
        $ciclos = Ciclo::active()->get();

        return view('admin.estructura.asignaturas.edit', compact('asignatura', 'ciclos'));
    }

    public function update(Request $request, Asignatura $asignatura): RedirectResponse
    {
        $validated = $request->validate([
            'ciclo_id' => ['required', 'exists:ciclos,id'],
            'codigo' => ['required', 'string', 'max:255', 'unique:asignaturas,codigo,' . $asignatura->id],
            'nombre' => ['required', 'string', 'max:255'],
            'horas_semanales' => ['required', 'integer', 'min:1', 'max:40'],
            'es_practicas' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $asignatura->update($validated);

        return redirect()->route('admin.estructura.asignaturas.index', $asignatura->ciclo_id)
            ->with('success', 'Asignatura actualizada correctamente.');
    }

    public function destroy(Asignatura $asignatura): RedirectResponse
    {
        $asignatura->delete();

        return redirect()->back()->with('success', 'Asignatura eliminada.');
    }
}
