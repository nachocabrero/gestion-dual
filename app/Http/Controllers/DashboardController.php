<?php

namespace App\Http\Controllers;

use App\Models\CursoAcademico;
use App\Models\Grupo;
use App\Models\Practica;
use App\Models\SolicitudPractica;
use App\Models\User;
use App\Support\StrSort;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Mostrar el panel principal (dashboard).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first()
            ?? CursoAcademico::orderBy('fecha_inicio', 'desc')->first();

        $isAdminOrCoord = $user ? $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL]) : false;
        $isProfesor = $user ? $user->hasRole(User::ROLE_PROFESOR) : false;

        $gruposPracticasStats = collect();

        if ($user && ($isAdminOrCoord || $isProfesor)) {
            $gruposQuery = Grupo::where('is_active', true);

            if ($cursoActual) {
                $gruposQuery->where(function ($q) use ($cursoActual) {
                    $q->where('curso_academico_id', $cursoActual->id)
                      ->orWhereNull('curso_academico_id');
                });
            }

            if (!$isAdminOrCoord && $isProfesor) {
                $profesorId = $user->profesor?->id;
                $gruposQuery->where(function ($q) use ($user, $profesorId) {
                    $q->where('tutor_id', $user->id);
                    if ($profesorId) {
                        $q->orWhereHas('profesores', fn($qp) => $qp->where('profesores.id', $profesorId));
                    }
                });
            }

            $grupos = $gruposQuery->with([
                'linea.ciclo',
                'tutor',
                'alumnos' => fn($q) => $q->whereHas('user', fn($u) => $u->where('is_active', true))
            ])
            ->get()
            ->sortBy(fn($g) => StrSort::normalize($g->nombre))
            ->values();

            $allAlumnoIds = $grupos->flatMap->alumnos->pluck('id')->unique();

            $solicitudesAceptadas = SolicitudPractica::whereIn('alumno_id', $allAlumnoIds)
                ->where('estado', 'aceptado')
                ->pluck('alumno_id');

            $practicasConOferta = Practica::whereIn('alumno_id', $allAlumnoIds)
                ->whereNotNull('oferta_id')
                ->pluck('alumno_id');

            $alumnosConOfertaGlobal = $solicitudesAceptadas->merge($practicasConOferta)->flip();
            $alumnosConPracticaGlobal = Practica::whereIn('alumno_id', $allAlumnoIds)->pluck('alumno_id')->flip();

            $gruposPracticasStats = $grupos->map(function ($grupo) use ($user, $alumnosConOfertaGlobal, $alumnosConPracticaGlobal) {
                $alumnosDelGrupo = $grupo->alumnos->filter(function ($a) use ($grupo) {
                    return !$grupo->curso_academico_id
                        || $a->pivot->curso_academico_id === null
                        || (int)$a->pivot->curso_academico_id === (int)$grupo->curso_academico_id;
                });

                $totalAlumnos = $alumnosDelGrupo->count();
                $conOferta = $alumnosDelGrupo->filter(fn($a) => $alumnosConOfertaGlobal->has($a->id))->count();
                $conPractica = $alumnosDelGrupo->filter(fn($a) => $alumnosConPracticaGlobal->has($a->id))->count();
                $esTutor = (int) $grupo->tutor_id === (int) $user->id;

                return [
                    'grupo' => $grupo,
                    'es_tutor' => $esTutor,
                    'total_alumnos' => $totalAlumnos,
                    'con_oferta' => $conOferta,
                    'con_practica' => $conPractica,
                ];
            });
        }

        return view('dashboard', compact('gruposPracticasStats', 'isAdminOrCoord', 'isProfesor'));
    }
}
