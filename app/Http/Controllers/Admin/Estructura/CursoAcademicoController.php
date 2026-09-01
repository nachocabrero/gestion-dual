<?php

namespace App\Http\Controllers\Admin\Estructura;

use App\Models\CursoAcademico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CursoAcademicoController
{
    /**
     * Listado de cursos académicos con su estado (activo/inactivo).
     */
    public function index(): View
    {
        $cursos = CursoAcademico::withCount(['proyectos'])->orderBy('fecha_inicio', 'desc')->paginate(20);

        return view('admin.estructura.cursos.index', compact('cursos'));
    }

    public function create(): View
    {
        return view('admin.estructura.cursos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:cursos_academicos,nombre'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        CursoAcademico::create($validated + ['is_active' => false]);

        return redirect()->route('admin.estructura.cursos.index')
            ->with('success', 'Curso académico creado correctamente.');
    }

    /**
     * Marca el curso como el actual (único activo), desactivando el resto.
     */
    public function setActive(CursoAcademico $curso): RedirectResponse
    {
        CursoAcademico::query()->update(['is_active' => false]);
        $curso->update(['is_active' => true]);

        return redirect()->route('admin.estructura.cursos.index')
            ->with('success', "Curso académico {$curso->nombre} marcado como curso actual.");
    }

    public function destroy(CursoAcademico $curso): RedirectResponse
    {
        if ($curso->is_active) {
            return back()->with('error', 'No se puede eliminar el curso académico actual. Marca primero otro como activo.');
        }

        if ($curso->proyectos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: el curso tiene proyectos asociados.');
        }

        $curso->delete();

        return redirect()->route('admin.estructura.cursos.index')
            ->with('success', 'Curso académico eliminado.');
    }
}
