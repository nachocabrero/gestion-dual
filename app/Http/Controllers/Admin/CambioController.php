<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cambio;
use App\Models\User;
use Illuminate\Http\Request;

class CambioController extends Controller
{
    public function index(Request $request)
    {
        $query = Cambio::with(['usuario', 'registrable'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('registrable_type')) {
            $query->where('registrable_type', $request->registrable_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('campo', 'like', "%{$search}%");
            });
        }

        $cambios = $query->paginate(50);

        // Opciones de filtro
        $usuarios = User::whereJsonContains('roles', 'admin')->get(['id', 'name', 'email']);
        $acciones = ['created', 'updated', 'deleted', 'estado_cambiado', 'asignado', 'anotado'];
        
        // Tipos de registrable disponibles
        $tiposRegistrable = [
            'App\\Models\\User' => 'Usuarios',
            'App\\Models\\Alumno' => 'Alumnos',
            'App\\Models\\Profesor' => 'Profesores',
            'App\\Models\\Empresa' => 'Empresas',
            'App\\Models\\Convenio' => 'Convenios',
            'App\\Models\\Proyecto' => 'Proyectos',
            'App\\Models\\Practica' => 'Prácticas',
            'App\\Models\\OfertaPractica' => 'Ofertas',
            'App\\Models\\SolicitudPractica' => 'Solicitudes',
            'App\\Models\\Ciclo' => 'Ciclos',
            'App\\Models\\Grupo' => 'Grupos',
            'App\\Models\\Calificacion' => 'Calificaciones',
            'App\\Models\\Anotacion' => 'Anotaciones',
        ];

        return view('admin.cambios.index', compact('cambios', 'usuarios', 'acciones', 'tiposRegistrable'));
    }

    public function show(Cambio $cambio)
    {
        $cambio->load(['usuario', 'registrable']);
        return view('admin.cambios.show', compact('cambio'));
    }
}