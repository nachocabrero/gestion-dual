<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Controlador para el auto-registro exclusivo de profesores (/teacher).
 * Las solicitudes quedan en estado de espera (is_active=false, registration_status=pending)
 * hasta su aprobación por administración o jefatura de estudios.
 */
class TeacherRegistrationController extends Controller
{
    /**
     * Mostrar el formulario de registro para profesores con familias y grupos disponibles.
     */
    public function create(): View
    {
        $familias = Familia::active()
            ->orderBy('nombre')
            ->get();

        $cursoActivo = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        $grupos = Grupo::where('is_active', true)
            ->when($cursoActivo, fn($q) => $q->where(fn($sub) => $sub->where('curso_academico_id', $cursoActivo->id)->orWhereNull('curso_academico_id')))
            ->with(['linea.ciclo.familia', 'cursoAcademico'])
            ->get()
            ->sortBy(fn($g) => $g->nombre)
            ->values();

        return view('auth.register-teacher', compact('familias', 'grupos'));
    }

    /**
     * Procesar la solicitud de registro de profesor.
     * RGPD: Consentimiento explícito obligatorio (Art. 6.1.a).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'familia_id' => ['nullable', 'exists:familias,id'],
            'tutor_grupo_id' => ['nullable', 'exists:grupos,id'],
            'grupos' => ['nullable', 'array'],
            'grupos.*' => ['exists:grupos,id'],
            'consent_rgpd' => ['required', 'accepted'],
            'privacy_policy' => ['required', 'accepted'],
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roles' => [User::ROLE_PROFESOR],
                'is_active' => false, // Queda inactivo en espera de aprobación de jefatura/admin
                'registration_status' => User::STATUS_PENDING,
                'registration_approved_at' => null,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]);

            $profesor = Profesor::create([
                'user_id' => $user->id,
                'familia_id' => $request->familia_id ?: null,
                'especialidad' => $request->especialidad ?: null,
                'es_tutor' => !empty($request->tutor_grupo_id),
                'es_coordinador_dual' => false,
            ]);

            $cursoActivo = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

            // Asignar como tutor del grupo si se ha seleccionado
            if ($request->filled('tutor_grupo_id')) {
                $grupoTutor = Grupo::find($request->tutor_grupo_id);
                if ($grupoTutor) {
                    $grupoTutor->update([
                        'tutor_id' => $user->id,
                        'curso_academico_id' => $grupoTutor->curso_academico_id ?: $cursoActivo?->id,
                    ]);
                }
            }

            // Asignar los grupos a los que imparte clase
            $gruposImpartidos = collect($request->input('grupos', []));
            if ($request->filled('tutor_grupo_id')) {
                $gruposImpartidos->push($request->tutor_grupo_id);
            }
            $gruposUnicos = $gruposImpartidos->unique()->filter()->values()->all();

            if (!empty($gruposUnicos)) {
                if ($cursoActivo) {
                    Grupo::whereIn('id', $gruposUnicos)->whereNull('curso_academico_id')->update(['curso_academico_id' => $cursoActivo->id]);
                }
                $profesor->gruposImpartidos()->sync($gruposUnicos);
            }

            event(new Registered($user));

            return redirect()->route('login')->with(
                'status',
                'Tu solicitud de registro como profesor se ha recibido correctamente. Tu cuenta está en estado de espera hasta que la administración o jefatura de estudios la verifique y active tu acceso.'
            );
        });
    }
}
