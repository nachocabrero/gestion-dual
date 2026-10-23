<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\TutorLaboral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de empresas.
 * Acceso: Admin.
 */
class EmpresaController extends Controller
{
    /**
     * Listar empresas.
     */
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $query = Empresa::with(['tutoresLaborales', 'ofertasPracticas', 'practicas', 'profesor.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('cif', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('familia')) {
            $query->byFamilia($request->familia);
        }

        if ($request->filled('profesor_id')) {
            if ($request->profesor_id === 'sin_asignar') {
                $query->sinProfesor();
            } else {
                $query->byProfesor((int) $request->profesor_id);
            }
        }

        $activeFilter = $request->input('active', '1');
        if ($activeFilter === '1') {
            $query->where('is_active', true);
        } elseif ($activeFilter === '0') {
            $query->where('is_active', false);
        }

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50])) {
            $perPage = 15;
        }

        $empresas = $query->orderBy('nombre')->paginate($perPage)->withQueryString();

        $profesores = Profesor::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get()
            ->sortBy('user.name')
            ->values();

        return view('empresas.index', compact('empresas', 'profesores'));
    }

    /**
     * Formulario crear empresa.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $profesores = Profesor::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get()
            ->sortBy('user.name')
            ->values();

        return view('empresas.create', compact('profesores'));
    }

    /**
     * Guardar empresa.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        // Filtrar tutores vacíos si se enviaron filas en blanco desde el formulario
        if ($request->has('tutores') && is_array($request->input('tutores'))) {
            $tutoresFiltrados = collect($request->input('tutores'))
                ->filter(function ($tutor) {
                    if (!is_array($tutor)) {
                        return false;
                    }
                    $nombre = trim($tutor['nombre'] ?? '');
                    $dni = trim($tutor['dni'] ?? '');
                    $email = trim($tutor['email'] ?? '');
                    $telefono = trim($tutor['telefono'] ?? '');
                    return $nombre !== '' || $dni !== '' || $email !== '' || $telefono !== '';
                })
                ->values()
                ->all();

            $request->merge(['tutores' => !empty($tutoresFiltrados) ? $tutoresFiltrados : null]);
        }

        $validated = $request->validate([
            'profesor_id' => ['nullable', 'exists:profesores,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'cif' => ['required', 'string', 'max:20', 'unique:empresas,cif'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable_nombre' => ['nullable', 'string', 'max:255'],
            'responsable_dni' => ['nullable', 'string', 'max:20'],
            'tutores' => ['nullable', 'array'],
            'tutores.*.nombre' => ['required', 'string', 'max:255'],
            'tutores.*.dni' => ['required', 'string', 'max:20'],
            'tutores.*.email' => ['nullable', 'email', 'max:255'],
            'tutores.*.telefono' => ['nullable', 'string', 'max:20'],
        ]);

        DB::beginTransaction();

        try {
            $empresaData = collect($validated)->except('tutores')->all();
            $empresa = Empresa::create($empresaData);

            // Tutores laborales
            if (!empty($validated['tutores'])) {
                foreach ($validated['tutores'] as $tutorData) {
                    TutorLaboral::create([
                        'empresa_id' => $empresa->id,
                        'nombre' => $tutorData['nombre'],
                        'dni' => $tutorData['dni'],
                        'email' => $tutorData['email'] ?? null,
                        'telefono' => $tutorData['telefono'] ?? null,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error creando empresa: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Error al crear la empresa: ' . $e->getMessage()]);
        }

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Empresa creada correctamente.');
    }

    /**
     * Ver empresa.
     */
    public function show(Empresa $empresa): View
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $empresa->load(['tutoresLaborales', 'profesor.user']);

        $cursoActual = \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        $ofertas = $empresa->ofertasPracticas()->with(['cursoAcademico'])->orderByDesc('created_at')->get();
        $practicas = $empresa->practicas()->with(['alumno.user', 'cursoAcademico', 'tutorLaboral'])->orderByDesc('fecha_inicio')->get();

        $bloques = $this->agruparPorCurso($ofertas, $practicas, $cursoActual);

        return view('empresas.show', compact('empresa', 'bloques', 'cursoActual'));
    }

    /**
     * Agrupa ofertas y prácticas de la empresa por curso académico.
     * El curso actual va primero; los anteriores quedan plegados en la vista.
     */
    private function agruparPorCurso($ofertas, $practicas, $cursoActual)
    {
        $grupos = [];

        foreach ($ofertas as $oferta) {
            $id = $oferta->curso_academico_id ?? ($cursoActual?->id ?? 'sin_curso');
            $grupos[$id]['ofertas'][] = $oferta;
        }

        foreach ($practicas as $practica) {
            $id = $practica->curso_academico_id ?? ($cursoActual?->id ?? 'sin_curso');
            $grupos[$id]['practicas'][] = $practica;
        }

        $cursos = \App\Models\CursoAcademico::orderByDesc('fecha_inicio')->get()->keyBy('id');

        $bloques = collect();

        foreach ($cursos as $curso) {
            $esActual = $cursoActual && $curso->id === $cursoActual->id;

            if (!isset($grupos[$curso->id]) && !$esActual) {
                continue;
            }

            $bloques->push((object) [
                'curso' => $curso,
                'es_actual' => $esActual,
                'ofertas' => collect($grupos[$curso->id]['ofertas'] ?? []),
                'practicas' => collect($grupos[$curso->id]['practicas'] ?? []),
            ]);
        }

        if (isset($grupos['sin_curso'])) {
            $bloques->push((object) [
                'curso' => null,
                'es_actual' => true,
                'ofertas' => collect($grupos['sin_curso']['ofertas'] ?? []),
                'practicas' => collect($grupos['sin_curso']['practicas'] ?? []),
            ]);
        }

        return $bloques;
    }

    /**
     * Formulario editar.
     */
    public function edit(Empresa $empresa): View
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $empresa->load(['tutoresLaborales', 'profesor.user']);

        $profesores = Profesor::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get()
            ->sortBy('user.name')
            ->values();

        return view('empresas.edit', compact('empresa', 'profesores'));
    }

    /**
     * Actualizar empresa.
     */
    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        // Filtrar tutores vacíos si se enviaron filas en blanco desde el formulario
        if ($request->has('tutores') && is_array($request->input('tutores'))) {
            $tutoresFiltrados = collect($request->input('tutores'))
                ->filter(function ($tutor) {
                    if (!is_array($tutor)) {
                        return false;
                    }
                    $nombre = trim($tutor['nombre'] ?? '');
                    $dni = trim($tutor['dni'] ?? '');
                    $email = trim($tutor['email'] ?? '');
                    $telefono = trim($tutor['telefono'] ?? '');
                    return $nombre !== '' || $dni !== '' || $email !== '' || $telefono !== '' || !empty($tutor['id']);
                })
                ->values()
                ->all();

            $request->merge(['tutores' => !empty($tutoresFiltrados) ? $tutoresFiltrados : null]);
        }

        $validated = $request->validate([
            'profesor_id' => ['nullable', 'exists:profesores,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'cif' => ['required', 'string', 'max:20', 'unique:empresas,cif,' . $empresa->id],
            'direccion' => ['nullable', 'string', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable_nombre' => ['nullable', 'string', 'max:255'],
            'responsable_dni' => ['nullable', 'string', 'max:20'],
            'tutores' => ['nullable', 'array'],
            'tutores.*.nombre' => ['required', 'string', 'max:255'],
            'tutores.*.dni' => ['required', 'string', 'max:20'],
            'tutores.*.email' => ['nullable', 'email', 'max:255'],
            'tutores.*.telefono' => ['nullable', 'string', 'max:20'],
            'tutores.*.id' => ['nullable', 'exists:tutores_laborales,id'],
        ]);

        DB::beginTransaction();

        try {
            $empresaData = collect($validated)->except('tutores')->all();
            $empresa->update($empresaData);

            // Tutores laborales
            if (isset($validated['tutores'])) {
                $tutorIds = [];
                foreach ($validated['tutores'] as $tutorData) {
                    if (!empty($tutorData['id'])) {
                        // Actualizar existente
                        $tutor = TutorLaboral::find($tutorData['id']);
                        if ($tutor) {
                            $tutor->update([
                                'nombre' => $tutorData['nombre'],
                                'dni' => $tutorData['dni'],
                                'email' => $tutorData['email'] ?? null,
                                'telefono' => $tutorData['telefono'] ?? null,
                            ]);
                            $tutorIds[] = $tutor->id;
                        }
                    } else {
                        // Crear nuevo
                        $tutor = TutorLaboral::create([
                            'empresa_id' => $empresa->id,
                            'nombre' => $tutorData['nombre'],
                            'dni' => $tutorData['dni'],
                            'email' => $tutorData['email'] ?? null,
                            'telefono' => $tutorData['telefono'] ?? null,
                        ]);
                        $tutorIds[] = $tutor->id;
                    }
                }
                // Eliminar tutores no enviados
                TutorLaboral::where('empresa_id', $empresa->id)
                    ->whereNotIn('id', $tutorIds)
                    ->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Empresa actualizada.');
    }

    /**
     * Añadir tutor laboral individual.
     */
    public function storeTutorLaboral(Request $request, Empresa $empresa): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        TutorLaboral::create([
            'empresa_id' => $empresa->id,
            ...$validated,
        ]);

        return back()->with('success', 'Tutor laboral añadido correctamente.');
    }

    /**
     * Actualizar tutor laboral individual.
     */
    public function updateTutorLaboral(Request $request, TutorLaboral $tutorLaboral): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $tutorLaboral->update($validated);

        return back()->with('success', 'Tutor laboral actualizado.');
    }

    /**
     * Eliminar tutor laboral individual.
     */
    public function destroyTutorLaboral(TutorLaboral $tutorLaboral): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $tutorLaboral->delete();

        return back()->with('success', 'Tutor laboral eliminado.');
    }

    /**
     * Desactivar empresa.
     */
    public function deactivate(Empresa $empresa): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $empresa->update(['is_active' => false]);
        return back()->with('success', 'Empresa desactivada.');
    }

    /**
     * Reactivar empresa.
     */
    public function reactivate(Empresa $empresa): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $empresa->update(['is_active' => true]);
        return back()->with('success', 'Empresa reactivada.');
    }

    /**
     * Eliminar empresa.
     */
    public function destroy(Empresa $empresa): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $empresa->delete();
        return redirect()->route('empresas.index')
            ->with('success', 'Empresa eliminada.');
    }
}