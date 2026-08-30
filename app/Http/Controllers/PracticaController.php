<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Practica;
use App\Models\TutorLaboral;
use App\Services\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de gestión de prácticas.
 */
class PracticaController extends Controller
{
    public function __construct(protected NotificacionService $notificacionService) {}
    /**
     * Listar prácticas.
     */
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $query = Practica::with(['alumno.user', 'empresa', 'tutorLaboral', 'cursoAcademico']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('alumno', function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'en_curso') {
                $query->enCurso();
            } elseif ($estado === 'finalizadas') {
                $query->finalizadas();
            } elseif ($estado === 'pendientes') {
                $query->pendientes();
            }
        }

        if ($request->filled('convenio')) {
            $query->where('convenio_firmado', $request->convenio === 'si');
        }

        $practicas = $query->paginate(30);
        $cursos = CursoAcademico::active()->get();

        return view('practicas.index', compact('practicas', 'cursos'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $alumnos = Alumno::with('user')->get();
        $empresas = Empresa::active()->get();
        $tutores = TutorLaboral::with('empresa')->get();
        $cursos = CursoAcademico::active()->get();

        return view('practicas.create', compact('alumnos', 'empresas', 'tutores', 'cursos'));
    }

    /**
     * Guardar práctica.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $errors = Practica::validarMinimoHoras($request->all());

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $validated = $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'empresa_id' => 'required|exists:empresas,id',
            'tutor_laboral_id' => 'nullable|exists:tutores_laborales,id',
            'curso_academico_id' => 'required|exists:cursos_academicos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'horas_acumuladas' => 'required|integer|min:0',
            'convenio_firmado' => 'boolean',
        ]);

        Practica::create($validated);

        return redirect()->route('practicas.index')->with('success', 'Práctica registrada correctamente.');
    }

    /**
     * Mostrar práctica.
     */
    public function show(Practica $practica): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $practica->load(['alumno.user', 'empresa', 'tutorLaboral', 'cursoAcademico']);

        return view('practicas.show', compact('practica'));
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Practica $practica): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $practica->load(['alumno', 'empresa', 'tutorLaboral', 'cursoAcademico']);
        $alumnos = Alumno::with('user')->get();
        $empresas = Empresa::active()->get();
        $tutores = TutorLaboral::with('empresa')->get();
        $cursos = CursoAcademico::active()->get();

        return view('practicas.edit', compact('practica', 'alumnos', 'empresas', 'tutores', 'cursos'));
    }

    /**
     * Actualizar práctica.
     */
    public function update(Request $request, Practica $practica): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $errors = Practica::validarMinimoHoras($request->all());

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $validated = $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'empresa_id' => 'required|exists:empresas,id',
            'tutor_laboral_id' => 'nullable|exists:tutores_laborales,id',
            'curso_academico_id' => 'required|exists:cursos_academicos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'horas_acumuladas' => 'required|integer|min:0',
            'convenio_firmado' => 'boolean',
        ]);

        $practica->update($validated);

        return redirect()->route('practicas.show', $practica)->with('success', 'Práctica actualizada correctamente.');
    }

    /**
     * Eliminar práctica.
     */
    public function destroy(Practica $practica): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $practica->delete();

        return redirect()->route('practicas.index')->with('success', 'Práctica eliminada correctamente.');
    }

    /**
     * Ver prácticas de un alumno.
     */
    public function misPracticas(): View
    {
        $alumno = auth()->user()->alumno;

        abort_unless($alumno, 403);

        $practicas = Practica::where('alumno_id', $alumno->id)
            ->with(['empresa', 'tutorLaboral', 'cursoAcademico'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('practicas.mis-practicas', compact('practicas'));
    }

    /**
     * Actualizar horas (para profesor/coordinador).
     */
    public function actualizarHoras(Request $request, Practica $practica): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $validated = $request->validate([
            'horas_acumuladas' => 'required|integer|min:0',
            'convenio_firmado' => 'boolean',
        ]);

        $practica->update($validated);

        // Notificar si se firma el convenio por primera vez
        if (isset($validated['convenio_firmado']) && $validated['convenio_firmado'] && !$practica->getRawOriginal('convenio_firmado')) {
            $this->notificacionService->acuerdoCambiado(
                $practica->alumno_id,
                'firmado'
            );
        }

        return back()->with('success', 'Horas actualizadas correctamente.');
    }
}