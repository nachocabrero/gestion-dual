<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\Proyecto;
use App\Models\ProyectoImagen;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProyectoController extends Controller
{
    /**
     * Mostrar proyectos.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole(User::ROLE_ADMIN)) {
            // Admin ve todos los proyectos
            $query = Proyecto::with(['alumno.user', 'ciclo', 'cursoAcademico', 'imagenes'])
                ->latest();

            if ($request->ciclo_id) {
                $query->where('ciclo_id', $request->ciclo_id);
            }
            if ($request->curso_academico_id) {
                $query->where('curso_academico_id', $request->curso_academico_id);
            }
            if ($request->search) {
                $query->where('titulo', 'like', "%{$request->search}%")
                    ->orWhere('descripcion', 'like', "%{$request->search}%");
            }

            $proyectos = $query->paginate(20);
            $ciclos = Ciclo::all();
            $cursos = CursoAcademico::active()->get();

            return view('proyectos.index', compact('proyectos', 'ciclos', 'cursos'));
        }

        if ($user->hasRole(User::ROLE_PROFESOR)) {
            // Profesor ve proyectos de los alumnos de sus grupos (tutor o equipo educativo)
            $profesor = Profesor::where('user_id', $user->id)->first();
            if (!$profesor) {
                return redirect()->route('dashboard')->with('error', 'No tienes perfil de profesor asociado.');
            }

            // Grupos donde es tutor + grupos de su equipo educativo
            $grupoIds = $profesor->gruposTutor()->pluck('id')->toArray();
            $grupoIdsEdu = $profesor->equiposEducativos()->pluck('id')->toArray();
            $todosGrupos = array_unique(array_merge($grupoIds, $grupoIdsEdu));

            if (empty($todosGrupos)) {
                $proyectos = collect([]);
            } else {
                $query = Proyecto::with(['alumno.user', 'ciclo', 'cursoAcademico', 'imagenes'])
                    ->whereHas('alumno', function ($q) use ($todosGrupos) {
                        $q->whereIn('grupo_id', $todosGrupos);
                    })
                    ->latest();

                if ($request->search) {
                    $query->where('titulo', 'like', "%{$request->search}%");
                }

                $proyectos = $query->paginate(20);
            }

            $ciclos = Ciclo::all();
            $cursos = CursoAcademico::active()->get();

            return view('proyectos.index', compact('proyectos', 'ciclos', 'cursos'));
        }

        // Alumno ve sus propios proyectos
        $alumno = Alumno::where('user_id', $user->id)->first();
        if (!$alumno) {
            return redirect()->route('dashboard')->with('error', 'No tienes perfil de alumno asociado.');
        }

        $proyectos = Proyecto::with(['imagenes'])
            ->where('alumno_id', $alumno->id)
            ->latest()
            ->get();

        return view('proyectos.index', compact('proyectos'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        $alumno = Alumno::where('user_id', Auth::id())->first();
        if (!$alumno) {
            return redirect()->route('dashboard')->with('error', 'No tienes perfil de alumno asociado.');
        }

        $ciclos = Ciclo::all();
        $cursos = CursoAcademico::active()->get();

        return view('proyectos.create', compact('ciclos', 'cursos'));
    }

    /**
     * Guardar nuevo proyecto.
     */
    public function store(Request $request): RedirectResponse
    {
        $alumno = Alumno::where('user_id', Auth::id())->first();
        if (!$alumno) {
            return redirect()->route('dashboard')->with('error', 'No tienes perfil de alumno asociado.');
        }

        $validated = $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'curso_academico_id' => 'required|exists:cursos_academicos,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:3000',
            'enlace_repositorio' => 'nullable|url|max:500',
            'enlace_despliegue' => 'nullable|url|max:500',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'descripcion.max' => 'La descripción no puede superar las 300 palabras.',
        ]);

        // Verificar máximo 300 palabras
        $wordCount = str_word_count($validated['descripcion']);
        if ($wordCount > 300) {
            return back()->withErrors(['descripcion' => "La descripción tiene {$wordCount} palabras. Máximo 300 permitidas."])
                ->withInput();
        }

        $proyecto = Proyecto::create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $validated['ciclo_id'],
            'curso_academico_id' => $validated['curso_academico_id'],
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'enlace_repositorio' => $validated['enlace_repositorio'] ?? null,
            'enlace_despliegue' => $validated['enlace_despliegue'] ?? null,
        ]);

        // Subir imágenes
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $image) {
                $path = $image->store('proyectos', 'public');
                ProyectoImagen::create([
                    'proyecto_id' => $proyecto->id,
                    'url' => $path,
                ]);
            }
        }

        return redirect()->route('proyectos.show', $proyecto)
            ->with('success', 'Proyecto creado correctamente.');
    }

    /**
     * Mostrar proyecto.
     */
    public function show(Proyecto $proyecto)
    {
        $proyecto->load(['imagenes', 'alumno.user', 'ciclo', 'cursoAcademico']);

        $user = Auth::user();
        if ($user->hasRole(User::ROLE_ALUMNO)) {
            $alumno = Alumno::where('user_id', $user->id)->first();
            if (!$alumno || $proyecto->alumno_id !== $alumno->id) {
                abort(403, 'No tienes permiso para ver este proyecto.');
            }
        }

        return view('proyectos.show', compact('proyecto'));
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Proyecto $proyecto)
    {
        $user = Auth::user();

        if ($user->hasRole(User::ROLE_ALUMNO)) {
            $alumno = Alumno::where('user_id', $user->id)->first();
            if (!$alumno || $proyecto->alumno_id !== $alumno->id) {
                abort(403, 'No tienes permiso para editar este proyecto.');
            }
            if ($proyecto->estaCalificado()) {
                return redirect()->route('proyectos.show', $proyecto)
                    ->with('error', 'No puedes editar un proyecto ya calificado.');
            }
        }

        $ciclos = Ciclo::all();
        $cursos = CursoAcademico::active()->get();

        return view('proyectos.edit', compact('proyecto', 'ciclos', 'cursos'));
    }

    /**
     * Actualizar proyecto.
     */
    public function update(Request $request, Proyecto $proyecto): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole(User::ROLE_ALUMNO)) {
            $alumno = Alumno::where('user_id', $user->id)->first();
            if (!$alumno || $proyecto->alumno_id !== $alumno->id) {
                abort(403);
            }
            if ($proyecto->estaCalificado()) {
                return redirect()->route('proyectos.show', $proyecto)
                    ->with('error', 'No puedes editar un proyecto ya calificado.');
            }
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:3000',
            'enlace_repositorio' => 'nullable|url|max:500',
            'enlace_despliegue' => 'nullable|url|max:500',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'eliminar_imagenes' => 'nullable|array',
            'eliminar_imagenes.*' => 'integer',
        ], [
            'descripcion.max' => 'La descripción no puede superar las 300 palabras.',
        ]);

        $wordCount = str_word_count($validated['descripcion']);
        if ($wordCount > 300) {
            return back()->withErrors(['descripcion' => "La descripción tiene {$wordCount} palabras. Máximo 300 permitidas."])
                ->withInput();
        }

        $proyecto->update([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'enlace_repositorio' => $validated['enlace_repositorio'] ?? null,
            'enlace_despliegue' => $validated['enlace_despliegue'] ?? null,
        ]);

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $image) {
                $path = $image->store('proyectos', 'public');
                ProyectoImagen::create([
                    'proyecto_id' => $proyecto->id,
                    'url' => $path,
                ]);
            }
        }

        if ($request->has('eliminar_imagenes')) {
            foreach ($request->input('eliminar_imagenes', []) as $imgId) {
                $img = ProyectoImagen::find($imgId);
                if ($img && $img->proyecto_id === $proyecto->id) {
                    Storage::disk('public')->delete($img->url);
                    $img->delete();
                }
            }
        }

        return redirect()->route('proyectos.show', $proyecto)
            ->with('success', 'Proyecto actualizado correctamente.');
    }

    /**
     * Calificar proyecto (solo profesor del módulo).
     */
    public function calificar(Request $request, Proyecto $proyecto): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->hasRole(User::ROLE_PROFESOR)) {
            abort(403, 'No tienes permiso para calificar proyectos.');
        }

        // Verificar que el profesor puede ver este proyecto (mismo grupo)
        $profesor = Profesor::where('user_id', $user->id)->first();
        if (!$profesor) {
            abort(403, 'No tienes perfil de profesor.');
        }

        // Grupos del profesor (tutor + equipo educativo)
        $grupoIds = $profesor->gruposTutor()->pluck('id')->toArray();
        $grupoIdsEdu = $profesor->equiposEducativos()->pluck('id')->toArray();
        $todosGrupos = array_unique(array_merge($grupoIds, $grupoIdsEdu));

        $alumnoProyecto = Alumno::find($proyecto->alumno_id);
        if (!$alumnoProyecto || empty($todosGrupos) || !in_array($alumnoProyecto->grupo_id, $todosGrupos)) {
            abort(403, 'No tienes permiso para calificar este proyecto.');
        }

        $validated = $request->validate([
            'calificacion' => 'required|numeric|min:1|max:10',
            'es_destacado' => 'nullable|boolean',
        ]);

        $proyecto->update([
            'calificacion' => $validated['calificacion'],
            'es_destacado' => $validated['es_destacado'] ?? false,
            'destacado_por_id' => ($validated['es_destacado'] ?? false) ? $user->id : null,
        ]);

        return redirect()->route('proyectos.show', $proyecto)
            ->with('success', 'Proyecto calificado correctamente.');
    }

    /**
     * Eliminar proyecto.
     */
    public function destroy(Proyecto $proyecto): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole(User::ROLE_ALUMNO)) {
            $alumno = Alumno::where('user_id', $user->id)->first();
            if (!$alumno || $proyecto->alumno_id !== $alumno->id) {
                abort(403);
            }
            if ($proyecto->estaCalificado()) {
                return redirect()->route('proyectos.show', $proyecto)
                    ->with('error', 'No puedes eliminar un proyecto ya calificado.');
            }
        }

        foreach ($proyecto->imagenes as $img) {
            Storage::disk('public')->delete($img->url);
        }

        $proyecto->delete();

        return redirect()->route('proyectos.index')
            ->with('success', 'Proyecto eliminado correctamente.');
    }

    /**
     * Portfolio público.
     */
    public function portfolio(Request $request)
    {
        $query = Proyecto::with(['imagenes', 'alumno.user', 'ciclo', 'cursoAcademico'])
            ->whereNotNull('calificacion')
            ->where('calificacion', '>=', 7);

        // Filtros
        if ($request->ciclo) {
            $query->where('ciclo_id', $request->ciclo);
        }
        if ($request->search) {
            $query->where('titulo', 'like', "%{$request->search}%")
                ->orWhere('descripcion', 'like', "%{$request->search}%");
        }

        $proyectos = $query->latest()->get();

        // Agrupar por ciclo
        $proyectosAgrupados = $proyectos->groupBy(function ($proyecto) {
            return $proyecto->ciclo->nombre ?? 'Sin ciclo';
        });

        // Estadísticas públicas
        $totalProyectos = $proyectos->count();
        $destacadosCount = $proyectos->where('es_destacado', true)->count();
        $promedioCalificacion = $proyectos->isNotEmpty()
            ? round($proyectos->avg('calificacion'), 1)
            : 0;

        // Nº de empresas colaboradoras (sin nombres)
        $totalEmpresas = Empresa::distinct()->count();

        // Ciclos para el filtro
        $ciclos = Ciclo::all();

        return view('proyectos.portfolio', compact(
            'proyectos',
            'proyectosAgrupados',
            'totalProyectos',
            'destacadosCount',
            'promedioCalificacion',
            'totalEmpresas',
            'ciclos'
        ));
    }
}