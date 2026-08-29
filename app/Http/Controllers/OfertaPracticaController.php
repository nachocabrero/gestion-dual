<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Empresa;
use App\Models\OfertaPractica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de ofertas y solicitudes de prácticas.
 */
class OfertaPracticaController extends Controller
{
    /**
     * Listar ofertas (según rol).
     */
    public function index(Request $request): View
    {
        $query = OfertaPractica::with(['empresa', 'creador']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('especialidad_requerida', 'like', "%{$search}%");
            });
        }

        if ($request->filled('especialidad')) {
            $query->porEspecialidad($request->especialidad);
        }

        if (auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO)) {
            // Alumno ve ofertas a las que se ha postulado o que están activas
            $alumno = auth()->user()->alumno;
            if ($alumno) {
                $query->where(function ($q) use ($alumno) {
                    $q->where('estado', 'activa')
                      ->orWhereHas('solicitudes', fn($sq) => $sq->where('alumno_id', $alumno->id));
                });
            }
        }

        $ofertas = $query->orderByDesc('created_at')->paginate(20);

        $especialidades = OfertaPractica::distinct()->pluck('especialidad_requerida')->toArray();

        return view('ofertas.index', compact('ofertas', 'especialidades'));
    }

    /**
     * Formulario crear oferta.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_EMPRESA]), 403);

        $empresas = Empresa::active()->get();

        return view('ofertas.create', compact('empresas'));
    }

    /**
     * Guardar oferta.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_EMPRESA]), 403);

        $validated = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'especialidad_requerida' => ['required', 'string', 'max:100'],
            'num_alumnos' => ['required', 'integer', 'min:1', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['creador_id'] = auth()->id();
        $validated['creador_type'] = User::class;
        $validated['estado'] = 'pendiente';

        $oferta = OfertaPractica::create($validated);

        return redirect()->route('ofertas.show', $oferta)
            ->with('success', 'Oferta creada correctamente.');
    }

    /**
     * Ver oferta.
     */
    public function show(OfertaPractica $oferta): View
    {
        $oferta->load(['empresa', 'creador', 'solicitudes.alumno']);

        return view('ofertas.show', compact('oferta'));
    }

    /**
     * Formulario editar.
     */
    public function edit(OfertaPractica $oferta): View
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $empresas = Empresa::active()->get();

        return view('ofertas.edit', compact('oferta', 'empresas'));
    }

    /**
     * Actualizar oferta.
     */
    public function update(Request $request, OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $validated = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'especialidad_requerida' => ['required', 'string', 'max:100'],
            'num_alumnos' => ['required', 'integer', 'min:1', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'estado' => ['required', 'in:pendiente,activa,cerrada'],
        ]);

        $oferta->update($validated);

        return redirect()->route('ofertas.show', $oferta)
            ->with('success', 'Oferta actualizada.');
    }

    /**
     * Eliminar oferta.
     */
    public function destroy(OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $oferta->delete();
        return redirect()->route('ofertas.index')
            ->with('success', 'Oferta eliminada.');
    }

    /**
     * Postularse a una oferta (alumno).
     */
    public function postularse(Request $request, OfertaPractica $oferta): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        $alumno = auth()->user()->alumno;
        abort_unless($alumno, 404);

        if ($oferta->estado !== 'activa') {
            return back()->withErrors(['error' => 'Esta oferta no está activa.']);
        }

        // Verificar si ya tiene una solicitud pendiente/aceptada
        $existing = SolicitudPractica::where('oferta_id', $oferta->id)
            ->where('alumno_id', $alumno->id)
            ->whereIn('estado', ['pendiente', 'aceptado'])
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'Ya tienes una solicitud para esta oferta.']);
        }

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud enviada correctamente.');
    }

    /**
     * Retirar solicitud (alumno).
     */
    public function retirar(Request $request, SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        if ($solicitud->alumno_id !== auth()->user()->alumno->id) {
            abort(403);
        }

        if ($solicitud->estado !== 'pendiente' && $solicitud->estado !== 'aceptado') {
            return back()->withErrors(['error' => 'No puedes retirar esta solicitud.']);
        }

        $solicitud->update(['estado' => 'retirado']);
        return back()->with('success', 'Solicitud retirada.');
    }

    /**
     * Aceptar solicitud (profesor/coordinador).
     */
    public function aceptar(SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_ADMIN]), 403);

        if ($solicitud->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La solicitud no está pendiente.']);
        }

        $solicitud->update(['estado' => 'aceptado']);
        return back()->with('success', 'Solicitud aceptada.');
    }

    /**
     * Rechazar solicitud (profesor/coordinador).
     */
    public function rechazar(Request $request, SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_ADMIN]), 403);

        if ($solicitud->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La solicitud no está pendiente.']);
        }

        $solicitud->update([
            'estado' => 'rechazado',
            'motivo_rechazo' => $request->input('motivo_rechazo'),
        ]);
        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * Ver solicitudes de una oferta (profesor/empresa).
     */
    public function solicitudes(OfertaPractica $oferta): View
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $solicitudes = $oferta->solicitudes()->with('alumno.user')->get();

        return view('ofertas.solicitudes', compact('oferta', 'solicitudes'));
    }

    /**
     * Ver ofertas de un alumno.
     */
    public function misOfertas(Request $request): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        $alumno = auth()->user()->alumno;
        abort_unless($alumno, 404);

        $solicitudes = $alumno->solicitudesPracticas()
            ->with('oferta.empresa')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('ofertas.mis-ofertas', compact('solicitudes'));
    }

    private function canEditOferta(OfertaPractica $oferta): bool
    {
        $user = auth()->user();

        // El creador puede editar
        if ($oferta->creador_id === $user->id && $oferta->creador_type === User::class) {
            return true;
        }

        // Admin puede editar todo
        if ($user->hasRole(\App\Models\User::ROLE_ADMIN)) {
            return true;
        }

        return false;
    }
}