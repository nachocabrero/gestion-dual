<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Convenio;
use App\Models\Empresa;
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
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $query = Empresa::with(['tutoresLaborales', 'convenios.alumno', 'convenios.grupo', 'convenios.tutorLaboral', 'convenios.tutorDocente']);

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

        if ($request->filled('active')) {
            $query->where('is_active', $request->active === '1');
        }

        $empresas = $query->orderBy('nombre')->paginate(20);

        $familias = Ciclo::with('familia')->get()->groupBy('familia.nombre')->keys();

        return view('empresas.index', compact('empresas', 'familias'));
    }

    /**
     * Formulario crear empresa.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $ciclos = Ciclo::with('familia')->get();

        return view('empresas.create', compact('ciclos'));
    }

    /**
     * Guardar empresa.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'cif' => ['required', 'string', 'max:20', 'unique:empresas,cif'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable_nombre' => ['nullable', 'string', 'max:255'],
            'responsable_dni' => ['nullable', 'string', 'max:20'],
            'tutores' => ['nullable', 'array'],
            'tutores.*.nombre' => ['required', 'string', 'max:255'],
            'tutores.*.email' => ['nullable', 'email', 'max:255'],
            'tutores.*.telefono' => ['nullable', 'string', 'max:20'],
            'convenios' => ['nullable', 'array'],
            'convenios.*.alumno_id' => ['nullable', 'integer'],
            'convenios.*.grupo_id' => ['nullable', 'integer'],
            'convenios.*.estado' => ['nullable', 'string', 'in:no_firmado,firmado'],
            'convenios.*.fecha_firma' => ['nullable', 'date'],
            'convenios.*.numero_horas' => ['nullable', 'integer'],
            'convenios.*.fecha_inicio' => ['nullable', 'date'],
            'convenios.*.fecha_fin' => ['nullable', 'date'],
        ]);

        DB::beginTransaction();

        try {
            $empresa = Empresa::create($validated);

            // Tutores laborales
            if (!empty($validated['tutores'])) {
                foreach ($validated['tutores'] as $tutorData) {
                    TutorLaboral::create([
                        'empresa_id' => $empresa->id,
                        ...$tutorData,
                    ]);
                }
            }

            // Convenios
            if (!empty($validated['convenios'])) {
                foreach ($validated['convenios'] as $convenioData) {
                    Convenio::create([
                        'empresa_id' => $empresa->id,
                        ...$convenioData,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la empresa: ' . $e->getMessage()]);
        }

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Empresa creada correctamente.');
    }

    /**
     * Ver empresa.
     */
    public function show(Empresa $empresa): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $empresa->load(['tutoresLaborales', 'convenios.alumno', 'convenios.grupo', 'convenios.tutorLaboral', 'convenios.tutorDocente']);

        return view('empresas.show', compact('empresa'));
    }

    /**
     * Formulario editar.
     */
    public function edit(Empresa $empresa): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $ciclos = Ciclo::with('familia')->get();
        $empresa->load(['tutoresLaborales', 'convenios.alumno', 'convenios.grupo', 'convenios.tutorLaboral', 'convenios.tutorDocente']);

        return view('empresas.edit', compact('empresa', 'ciclos'));
    }

    /**
     * Actualizar empresa.
     */
    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'cif' => ['required', 'string', 'max:20', 'unique:empresas,cif,' . $empresa->id],
            'direccion' => ['nullable', 'string', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable_nombre' => ['nullable', 'string', 'max:255'],
            'responsable_dni' => ['nullable', 'string', 'max:20'],
            'tutores' => ['nullable', 'array'],
            'tutores.*.nombre' => ['required', 'string', 'max:255'],
            'tutores.*.email' => ['nullable', 'email', 'max:255'],
            'tutores.*.telefono' => ['nullable', 'string', 'max:20'],
            'tutores.*.id' => ['nullable', 'exists:tutores_laborales,id'],
            'convenios' => ['nullable', 'array'],
            'convenios.*.id' => ['nullable', 'integer'],
            'convenios.*.alumno_id' => ['nullable', 'integer'],
            'convenios.*.grupo_id' => ['nullable', 'integer'],
            'convenios.*.estado' => ['nullable', 'string', 'in:no_firmado,firmado'],
            'convenios.*.fecha_firma' => ['nullable', 'date'],
            'convenios.*.numero_horas' => ['nullable', 'integer'],
            'convenios.*.fecha_inicio' => ['nullable', 'date'],
            'convenios.*.fecha_fin' => ['nullable', 'date'],
        ]);

        DB::beginTransaction();

        try {
            $empresa->update($validated);

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

            // Convenios
            if (isset($validated['convenios'])) {
                $convenioIds = [];
                foreach ($validated['convenios'] as $convenioData) {
                    if (!empty($convenioData['id'])) {
                        // Actualizar existente
                        $convenio = Convenio::find($convenioData['id']);
                        if ($convenio && $convenio->empresa_id === $empresa->id) {
                            $convenio->update(array_filter($convenioData, fn($k) => !in_array($k, ['id']), ARRAY_FILTER_USE_KEY));
                            $convenioIds[] = $convenio->id;
                        }
                    } else {
                        // Crear nuevo
                        Convenio::create([
                            'empresa_id' => $empresa->id,
                            ...$convenioData,
                        ]);
                    }
                }
                // Eliminar convenios no enviados
                Convenio::where('empresa_id', $empresa->id)
                    ->whereNotIn('id', $convenioIds)
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