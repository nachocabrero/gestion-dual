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
        $grupos = Grupo::where('is_active', true)
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
            'role' => ['required', 'in:alumno,profesor,coordinador_dual,empresa'],
            'consent_rgpd' => ['required', 'accepted'],
            'privacy_policy' => ['required', 'accepted'],
        ];

        if ($request->role === 'alumno') {
            $rules['grupos'] = ['nullable', 'array'];
            $rules['grupos.*'] = ['exists:grupos,id'];
        }

        $request->validate($rules);

        return DB::transaction(function () use ($request) {
            $isAlumno = $request->role === 'alumno';

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roles' => [$request->role],
                'is_active' => ! $isAlumno, // Alumnos nacen inactivos/en espera
                'registration_status' => $isAlumno ? User::STATUS_PENDING : User::STATUS_APPROVED,
                'registration_approved_at' => $isAlumno ? null : now(),
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]);

            if ($isAlumno) {
                $alumno = Alumno::create([
                    'user_id' => $user->id,
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
                    'Tu registro se ha completado correctamente. Tu cuenta está en estado de espera hasta que un administrador la revise y apruebe.'
                );
            }

            event(new Registered($user));

            Auth::login($user);

            return redirect()->route('dashboard');
        });
    }
}