<?php

namespace App\Http\Controllers;

use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Alumno;
use App\Models\Profesor;
use App\Models\TutorLaboral;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConvenioController extends Controller
{
    public function create(Empresa $empresa): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $alumnos = Alumno::with('user')->get();
        $profesores = Profesor::with('user')->get();
        $tutores = $empresa->tutoresLaborales;
        $grupos = Grupo::with('ciclo')->get();

        return view('convenios.create', compact('empresa', 'alumnos', 'profesores', 'tutores', 'grupos'));
    }

    public function store(Request $request, Empresa $empresa): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $validated = $request->validate([
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'tutor_laboral_id' => ['required', 'exists:tutores_laborales,id'],
            'tutor_docente_id' => ['required', 'exists:profesores,id'],
            'grupo_id' => ['required', 'exists:grupos,id'],
            'numero_horas' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', 'in:no_firmado,firmado'],
            'fecha_firma' => ['nullable', 'date'],
        ]);

        $validated['empresa_id'] = $empresa->id;

        Convenio::create($validated);

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Convenio creado correctamente.');
    }

    public function edit(Empresa $empresa, Convenio $convenio): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        if ($convenio->empresa_id !== $empresa->id) {
            abort(404);
        }

        $alumnos = Alumno::with('user')->get();
        $profesores = Profesor::with('user')->get();
        $tutores = $empresa->tutoresLaborales;
        $grupos = Grupo::with('ciclo')->get();

        return view('convenios.edit', compact('empresa', 'convenio', 'alumnos', 'profesores', 'tutores', 'grupos'));
    }

    public function update(Request $request, Empresa $empresa, Convenio $convenio): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        if ($convenio->empresa_id !== $empresa->id) {
            abort(404);
        }

        $validated = $request->validate([
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'tutor_laboral_id' => ['required', 'exists:tutores_laborales,id'],
            'tutor_docente_id' => ['required', 'exists:profesores,id'],
            'grupo_id' => ['required', 'exists:grupos,id'],
            'numero_horas' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', 'in:no_firmado,firmado'],
            'fecha_firma' => ['nullable', 'date'],
        ]);

        $convenio->update($validated);

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Convenio actualizado correctamente.');
    }

    public function destroy(Empresa $empresa, Convenio $convenio): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        if ($convenio->empresa_id !== $empresa->id) {
            abort(404);
        }

        $convenio->delete();

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Convenio eliminado correctamente.');
    }
}
