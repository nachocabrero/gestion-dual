<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Mostrar el formulario de registro con la lista de grupos activos.
     */
    public function create(): View
    {
        $cursoActivo = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        $grupos = Grupo::where('is_active', true)
            ->when($cursoActivo, fn($q) => $q->where(fn($sub) => $sub->where('curso_academico_id', $cursoActivo->id)->orWhereNull('curso_academico_id')))
            ->with(['linea.ciclo.familia', 'cursoAcademico'])
            ->get()
            ->sortBy(fn($g) => $g->nombre)
            ->values();

        return view('auth.register', compact('grupos'));
    }

    /**
     * Manejar un registro entrante.
     * RGPD: Consentimiento explícito obligatorio (Art. 6.1.a)
     * Alumnos: Quedan en estado de espera (is_active=false, registration_status=pending)
     * hasta aprobación por parte del administrador.
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:alumno'],
            'grupos' => ['nullable', 'array'],
            'grupos.*' => ['exists:grupos,id'],
            'empresa_practicas_primero' => ['nullable', 'string', 'max:255'],
            'horas_practicas_primero' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'sin_practicas_primero' => ['nullable', 'boolean'],
            'repitiendo_modulos_primero' => ['nullable', 'boolean'],
            'consent_rgpd' => ['required', 'accepted'],
            'privacy_policy' => ['required', 'accepted'],
        ];

        $request->validate($rules);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roles' => [User::ROLE_ALUMNO],
                'is_active' => false, // Alumnos nacen inactivos/en espera
                'registration_status' => User::STATUS_PENDING,
                'registration_approved_at' => null,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]);

            $sinPracticas = $request->boolean('sin_practicas_primero');

            $alumno = Alumno::create([
                'user_id' => $user->id,
                'sin_practicas_primero' => $sinPracticas,
                'repitiendo_modulos_primero' => $request->boolean('repitiendo_modulos_primero'),
                'empresa_practicas_primero' => $sinPracticas ? null : ($request->empresa_practicas_primero ?: null),
                'horas_practicas_primero' => $sinPracticas ? 0 : ($request->filled('horas_practicas_primero') ? (int) $request->horas_practicas_primero : null),
            ]);

            // Asignar los grupos seleccionados
            if ($request->filled('grupos') && is_array($request->grupos)) {
                $cursoActivo = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

                foreach ($request->grupos as $grupoId) {
                    $grupo = Grupo::find($grupoId);
                    $cursoId = $grupo?->curso_academico_id ?? $cursoActivo?->id;

                    $alumno->grupos()->attach($grupoId, [
                        'curso_academico_id' => $cursoId,
                    ]);
                }
            }

            event(new Registered($user));

            // No iniciar sesión automáticamente. Redirigir a login con aviso de espera
            return redirect()->route('login')->with(
                'status',
                'Tu registro como alumno se ha completado correctamente. Tu cuenta está en estado de espera hasta que un administrador la revise y apruebe.'
            );
        });
    }
}