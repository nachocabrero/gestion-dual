<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Practica;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Panel de administración principal.
     */
    public function index(Request $request): View
    {
        // Total usuarios por rol y estado
        $totalAlumnos = User::where('roles', 'like', '%alumno%')->count();
        $totalAlumnosActivos = User::where('roles', 'like', '%alumno%')->where('is_active', true)->count();
        $totalAlumnosInactivos = User::where('roles', 'like', '%alumno%')->where('is_active', false)->count();

        $totalProfesores = User::where('roles', 'like', '%profesor%')->count();
        $totalProfesoresActivos = User::where('roles', 'like', '%profesor%')->where('is_active', true)->count();
        $totalProfesoresInactivos = User::where('roles', 'like', '%profesor%')->where('is_active', false)->count();

        $totalEmpresas = Empresa::where('is_active', true)->count();
        $totalEmpresasInactivas = Empresa::where('is_active', false)->count();

        // Prácticas por estado
        $practicasEnCurso = Practica::enCurso()->count();
        $practicasPendientes = Practica::pendientes()->count();
        $practicasFinalizadas = Practica::finalizadas()->count();

        // Convenios
        $conveniosFirmados = Convenio::firmados()->count();
        $conveniosNoFirmados = Convenio::noFirmados()->count();

        // Convenios filtrables
        $conveniosQuery = Convenio::with('empresa');
        if ($request->filled('convenio_familia')) {
            // Filtro eliminado: convenios ya no tienen ciclo_id
        }
        if ($request->filled('convenio_curso')) {
            // Filtro eliminado: convenios ya no tienen curso_academico
        }
        $convenios = $conveniosQuery->paginate(50);

        // Proyectos destacados por ciclo
        $proyectosDestacados = Proyecto::whereNotNull('calificacion')
            ->where('es_destacado', true)
            ->with(['alumno.user', 'ciclo'])
            ->latest()
            ->limit(10)
            ->get()
            ->groupBy(fn($p) => $p->ciclo->nombre ?? 'Sin ciclo');

        // Proyectos destacados por ciclo (conteo)
        $destacadosPorCiclo = Proyecto::whereNotNull('calificacion')
            ->where('es_destacado', true)
            ->with('ciclo')
            ->get()
            ->groupBy(fn($p) => $p->ciclo->nombre ?? 'Sin ciclo')
            ->map(fn($items) => $items->count());

        // Actividad reciente (últimas notificaciones y cambios)
        $actividadReciente = \App\Models\Notificacion::with('usuario')
            ->where(function ($q) {
                $q->whereNull('expira_en')
                  ->orWhere('expira_en', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // Estadísticas de ciclos
        $ciclos = \App\Models\Alumno::whereHas('user', fn($q) => $q->where('is_active', true))
            ->with('ciclosMatriculados')
            ->get()
            ->flatMap(fn($alumno) => $alumno->ciclosMatriculados->map(fn($ciclo) => (object) [
                'nombre' => $ciclo->nombre,
            ]))
            ->groupBy('nombre')
            ->map(fn($items) => (object) [
                'nombre' => $items->first()->nombre,
                'alumnos_count' => $items->count(),
            ])
            ->values();

        return view('admin.dashboard', compact(
            'totalAlumnos', 'totalAlumnosActivos', 'totalAlumnosInactivos',
            'totalProfesores', 'totalProfesoresActivos', 'totalProfesoresInactivos',
            'totalEmpresas', 'totalEmpresasInactivas',
            'practicasEnCurso', 'practicasPendientes', 'practicasFinalizadas',
            'conveniosFirmados', 'conveniosNoFirmados',
            'convenios',
            'proyectosDestacados',
            'destacadosPorCiclo',
            'actividadReciente',
            'ciclos'
        ));
    }
}