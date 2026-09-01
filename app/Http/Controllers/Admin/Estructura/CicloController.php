<?php

namespace App\Http\Controllers\Admin\Estructura;

use App\Models\Ciclo;
use App\Models\Familia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CicloController
{
    /**
     * Ciclos dentro de una familia.
     */
    public function index(Familia $familia): View
    {
        $ciclos = $familia->ciclos()->withCount(['lineas', 'asignaturas'])->orderBy('nombre')->get();

        return view('admin.estructura.ciclos.index', compact('familia', 'ciclos'));
    }

    public function create(Familia $familia): View
    {
        return view('admin.estructura.ciclos.create', compact('familia'));
    }

    public function store(Request $request, Familia $familia): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:255', 'unique:ciclos,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'grado' => ['required', 'in:basica,media,superior,especializacion,acreditacion'],
            'duracion_anos' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $familia->ciclos()->create($validated);

        return redirect()->route('admin.estructura.ciclos.index', $familia)
            ->with('success', 'Ciclo creado correctamente.');
    }

    public function show(Ciclo $ciclo): View
    {
        $ciclo->load(['familia', 'lineas.grupos', 'asignaturas']);

        return view('admin.estructura.ciclos.show', compact('ciclo'));
    }

    public function edit(Ciclo $ciclo): View
    {
        $familias = Familia::orderBy('nombre')->get();

        return view('admin.estructura.ciclos.edit', compact('ciclo', 'familias'));
    }

    public function update(Request $request, Ciclo $ciclo): RedirectResponse
    {
        $validated = $request->validate([
            'familia_id' => ['required', 'exists:familias,id'],
            'codigo' => ['required', 'string', 'max:255', 'unique:ciclos,codigo,' . $ciclo->id],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'grado' => ['required', 'in:basica,media,superior,especializacion,acreditacion'],
            'duracion_anos' => ['required', 'integer', 'min:1', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ciclo->update($validated);

        return redirect()->route('admin.estructura.ciclos.show', $ciclo)
            ->with('success', 'Ciclo actualizado correctamente.');
    }

    public function destroy(Ciclo $ciclo): RedirectResponse
    {
        if ($ciclo->lineas()->count() > 0 || $ciclo->asignaturas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: el ciclo tiene líneas o asignaturas asociadas.');
        }

        $ciclo->delete();

        return redirect()->route('admin.estructura.familias.index')
            ->with('success', 'Ciclo eliminado.');
    }
}
