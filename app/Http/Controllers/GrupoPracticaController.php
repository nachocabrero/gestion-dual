<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Grupo;
use App\Models\OfertaPractica;
use App\Models\Practica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de prácticas por grupo.
 * Acceso: admin, coordinador dual, profesores del grupo.
 */
class GrupoPracticaController extends Controller
{
    /**
     * Listar grupos del curso activo (para elegir).
     */
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->hasAnyRole([
            User::ROLE_ADMIN,
            User::ROLE_COORDINADOR_DUAL,
            User::ROLE_PROFESOR,
        ]), 403);

        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        $isAdminOrCoordinador = auth()->user()->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL]);

        if (!$isAdminOrCoordinador && auth()->user()->hasRole(User::ROLE_PROFESOR)) {
            $grupos = Grupo::where('curso_academico_id', $cursoActual?->id)
                ->whereHas('profesores', fn($q) => $q->where('profesores.id', auth()->user()->profesor?->id))
                ->with('linea')
                ->orderBy('nombre')
                ->get();
        } else {
            $grupos = Grupo::where('curso_academico_id', $cursoActual?->id)
                ->with('linea')
                ->orderBy('nombre')
                ->get();
        }

        $selectedGroupId = $request->input('grupo');

        return view('practicas.grupos.index', compact('grupos', 'selectedGroupId'));
    }

    /**
     * Mostrar la vista de prácticas por grupo.
     */
    public function show(Grupo $grupo): View
    {
        abort_unless(auth()->user()->hasAnyRole([
            User::ROLE_ADMIN,
            User::ROLE_COORDINADOR_DUAL,
            User::ROLE_PROFESOR,
        ]), 403);

        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        $isAdminOrCoordinador = auth()->user()->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL]);

        // Verificar permisos: profesor solo puede ver sus grupos
        if (!$isAdminOrCoordinador && auth()->user()->hasRole(User::ROLE_PROFESOR)) {
            $esProfesorGrupo = $grupo->profesores()->where('profesores.id', auth()->user()->profesor?->id)->exists();
            abort_unless($esProfesorGrupo, 403);
        }

        // Verificar que el grupo pertenece al curso actual
        abort_unless($grupo->curso_academico_id === $cursoActual?->id, 404);

        // ── 1. Alumnos sin práctica asignada ──
        $alumnosSinPractica = $this->getAlumnosSinPractica($grupo, $cursoActual);

        // ── 2. Empresas/ofertas con plazas libres ──
        $ofertasConPlazas = $this->getOfertasConPlazasLibres($grupo, $cursoActual);

        // ── 3. Alumnos con práctica asignada ──
        $alumnosConPractica = $this->getAlumnosConPractica($grupo, $cursoActual);

        return view('practicas.grupos.show', compact(
            'grupo',
            'alumnosSinPractica',
            'ofertasConPlazas',
            'alumnosConPractica'
        ));
    }

    /**
     * Alumnos del grupo sin práctica asignada, ordenados por media de anotaciones.
     * Incluye info de ofertas aceptadas.
     */
    private function getAlumnosSinPractica(Grupo $grupo, ?CursoAcademico $cursoActual): array
    {
        // Alumnos del grupo en este curso
        $alumnos = $grupo->alumnosEnCurso($cursoActual?->id)->get();

        // Excluir los que ya tienen práctica en este curso
        $alumnosConPracticaIds = Practica::where('curso_academico_id', $cursoActual?->id)
            ->whereIn('alumno_id', $alumnos->pluck('id'))
            ->pluck('alumno_id')
            ->toArray();

        $alumnos = $alumnos->reject(fn($a) => in_array($a->id, $alumnosConPracticaIds));

        // Calcular media de puestos y ofertas aceptadas
        $resultado = [];
        foreach ($alumnos as $alumno) {
            $mediaPuesto = $alumno->anotaciones()->whereNotNull('puesto')->avg('puesto');

            // Solicitudes aceptadas (no retiradas/rechazadas)
            $solicitudesAceptadas = SolicitudPractica::where('alumno_id', $alumno->id)
                ->where('estado', 'aceptado')
                ->with('oferta.empresa')
                ->get();

            $resultado[] = [
                'alumno' => $alumno,
                'media_puesto' => $mediaPuesto ? round($mediaPuesto, 2) : null,
                'ofertas_aceptadas' => $solicitudesAceptadas,
                'tiene_oferta_aceptada' => $solicitudesAceptadas->isNotEmpty(),
            ];
        }

        // Ordenar: primero los que tienen media_puesto (mejor = menor), luego los sin anotaciones
        usort($resultado, function ($a, $b) {
            // Si ambos tienen media, ordenar por media ascendente
            if ($a['media_puesto'] !== null && $b['media_puesto'] !== null) {
                return $a['media_puesto'] <=> $b['media_puesto'];
            }
            // Los que tienen media van primero
            if ($a['media_puesto'] !== null && $b['media_puesto'] === null) {
                return -1;
            }
            if ($a['media_puesto'] === null && $b['media_puesto'] !== null) {
                return 1;
            }
            // Sin media: orden alfabético
            return strcmp($a['alumno']->user->name, $b['alumno']->user->name);
        });

        return $resultado;
    }

    /**
     * Ofertas activas para este grupo con plazas libres.
     */
    private function getOfertasConPlazasLibres(Grupo $grupo, ?CursoAcademico $cursoActual): array
    {
        // Ofertas dirigidas a este grupo y activas
        $ofertas = OfertaPractica::where('estado', 'activa')
            ->where('curso_academico_id', $cursoActual?->id)
            ->whereHas('grupos', fn($q) => $q->where('grupos.id', $grupo->id))
            ->with(['empresa'])
            ->get();

        $resultado = [];
        $totalPlazasLibres = 0;

        foreach ($ofertas as $oferta) {
            // Plazas asignadas = solicitudes aceptadas
            $plazasAsignadas = SolicitudPractica::where('oferta_id', $oferta->id)
                ->where('estado', 'aceptado')
                ->count();

            $plazasLibres = $oferta->num_alumnos - $plazasAsignadas;

            if ($plazasLibres > 0) {
                $resultado[] = [
                    'oferta' => $oferta,
                    'plazas_libres' => $plazasLibres,
                    'plazas_asignadas' => $plazasAsignadas,
                    'total_plazas' => $oferta->num_alumnos,
                ];
                $totalPlazasLibres += $plazasLibres;
            }
        }

        return [
            'ofertas' => $resultado,
            'total_libres' => $totalPlazasLibres,
        ];
    }

    /**
     * Alumnos del grupo con práctica asignada.
     */
    private function getAlumnosConPractica(Grupo $grupo, ?CursoAcademico $cursoActual): array
    {
        $alumnosIds = $grupo->alumnosEnCurso($cursoActual?->id)->pluck('alumnos.id')->toArray();

        $practicas = Practica::whereIn('alumno_id', $alumnosIds)
            ->where('curso_academico_id', $cursoActual?->id)
            ->with(['alumno.user', 'empresa'])
            ->get()
            ->sortBy(fn($p) => $p->alumno->user->name ?? '');

        return $practicas->map(fn($p) => [
            'practica' => $p,
            'alumno' => $p->alumno,
            'empresa' => $p->empresa,
        ])->toArray();
    }
}
