<?php

namespace App\Http\Controllers\Admin\Estructura;

use App\Models\Familia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamiliaController
{
    /**
     * Lista de familias profesionales.
     */
    public function index(): View
    {
        $familias = Familia::withCount('ciclos')->orderBy('nombre')->paginate(50);

        return view('admin.estructura.familias.index', compact('familias'));
    }

    public function create(): View
    {
        return view('admin.estructura.familias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:255', 'unique:familias,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ]);

        Familia::create($validated);

        return redirect()->route('admin.estructura.familias.index')
            ->with('success', 'Familia creada correctamente.');
    }

    public function show(Familia $familia): View
    {
        $familia->load(['ciclos.lineas']);

        return view('admin.estructura.familias.show', compact('familia'));
    }

    public function edit(Familia $familia): View
    {
        return view('admin.estructura.familias.edit', compact('familia'));
    }

    public function update(Request $request, Familia $familia): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:255', 'unique:familias,codigo,' . $familia->id],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $familia->update($validated);

        return redirect()->route('admin.estructura.familias.show', $familia)
            ->with('success', 'Familia actualizada correctamente.');
    }

    public function destroy(Familia $familia): RedirectResponse
    {
        if ($familia->ciclos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: la familia tiene ciclos asociados.');
        }

        $familia->delete();

        return redirect()->route('admin.estructura.familias.index')
            ->with('success', 'Familia eliminada.');
    }
}
