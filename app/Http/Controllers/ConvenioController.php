<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\TutorLaboral;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConvenioController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $empresaId = $request->input('empresa_id') ?? $request->route('empresa');
        $empresa = $empresaId ? Empresa::find($empresaId) : null;
        if (!$empresa) {
            $empresa = Empresa::first() ?? new Empresa();
        }

        $alumnos = Alumno::with('user')->get();
        $profesores = Profesor::active()
            ->with('user')
            ->get()
            ->sortBy(fn($p) => \App\Support\StrSort::normalize($p->user?->name ?? ''))
            ->values();
        $tutores = $empresa->exists ? $empresa->tutoresLaborales : TutorLaboral::all();
        $grupos = Grupo::with(['ciclo', 'linea.ciclo'])->get();

        return view('convenios.create', compact('empresa', 'alumnos', 'profesores', 'tutores', 'grupos'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $empresaId = $request->input('empresa_id') ?? $request->route('empresa');
        if ($empresaId instanceof Empresa) {
            $empresaId = $empresaId->id;
        }

        $validated = $request->validate([
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'tutor_laboral_id' => ['required', 'exists:tutores_laborales,id'],
            'tutor_docente_id' => [
                'required',
                Rule::exists('profesores', 'id')->where(function ($query) {
                    $query->whereIn('user_id', User::active()->select('id'));
                }),
            ],
            'grupo_id' => ['required', 'exists:grupos,id'],
            'numero_horas' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', 'in:no_firmado,firmado'],
            'fecha_firma' => ['nullable', 'date'],
        ]);

        $finalEmpresaId = $validated['empresa_id'] ?? $empresaId ?? Empresa::first()?->id;
        $validated['empresa_id'] = $finalEmpresaId;

        $convenio = Convenio::create($validated);

        return redirect()->route('empresas.show', $convenio->empresa_id)
            ->with('success', 'Convenio creado correctamente.');
    }

    public function edit(Convenio $convenio): View
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $empresa = $convenio->empresa ?? Empresa::first() ?? new Empresa();
        $alumnos = Alumno::with('user')->get();
        $profesores = Profesor::active()
            ->with('user')
            ->get()
            ->sortBy(fn($p) => \App\Support\StrSort::normalize($p->user?->name ?? ''))
            ->values();
        $tutores = $empresa->exists ? $empresa->tutoresLaborales : TutorLaboral::all();
        $grupos = Grupo::with(['ciclo', 'linea.ciclo'])->get();

        return view('convenios.edit', compact('empresa', 'convenio', 'alumnos', 'profesores', 'tutores', 'grupos'));
    }

    public function update(Request $request, Convenio $convenio): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $validated = $request->validate([
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'tutor_laboral_id' => ['required', 'exists:tutores_laborales,id'],
            'tutor_docente_id' => [
                'required',
                Rule::exists('profesores', 'id')->where(function ($query) {
                    $query->whereIn('user_id', User::active()->select('id'));
                }),
            ],
            'grupo_id' => ['required', 'exists:grupos,id'],
            'numero_horas' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', 'in:no_firmado,firmado'],
            'fecha_firma' => ['nullable', 'date'],
        ]);

        $convenio->update($validated);

        return redirect()->route('empresas.show', $convenio->empresa_id)
            ->with('success', 'Convenio actualizado correctamente.');
    }

    public function destroy(Convenio $convenio): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $empresaId = $convenio->empresa_id;
        $convenio->delete();

        return redirect()->route('empresas.show', $empresaId)
            ->with('success', 'Convenio eliminado correctamente.');
    }
}
