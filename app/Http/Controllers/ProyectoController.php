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
use App\Services\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProyectoController extends Controller
{
    public function __construct(protected NotificacionService $notificacionService) {}
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

            $proyectos = $query->paginate(50);
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
            $grupoIdsEdu = $profesor->gruposImpartidos()->pluck('grupos.id')->toArray();
            $todosGrupos = array_unique(array_merge($grupoIds, $grupoIdsEdu));

            if (empty($todosGrupos)) {
                $proyectos = collect([]);
            } else {
                $query = Proyecto::with(['alumno.user', 'ciclo', 'cursoAcademico', 'imagenes'])
                    ->whereHas('alumno', function ($q) use ($todosGrupos) {
                        $q->whereHas('grupos', function($q) use ($todosGrupos) {
                            $q->whereIn('grupos.id', $todosGrupos);
                        });
                    })
                    ->latest();

                if ($request->search) {
                    $query->where('titulo', 'like', "%{$request->search}%");
                }

                $proyectos = $query->paginate(50);
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

        $ciclos = $alumno->ciclosMatriculados;
        $cursos = CursoAcademico::active()->get();
        $grupos = $alumno->grupos;

        return view('proyectos.create', compact('ciclos', 'cursos', 'grupos'));
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
            'grupo_id' => 'required|exists:grupos,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:3000',
            'enlace_repositorio' => 'nullable|url|max:500',
            'enlace_despliegue' => 'nullable|url|max:500',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'descripcion.max' => 'La descripción no puede superar las 300 palabras.',
        ]);

        $grupo = Grupo::with('linea')->findOrFail($validated['grupo_id']);
        $ciclo_id = $grupo->linea->ciclo_id ?? null;

        if (!$ciclo_id) {
            return back()->withErrors(['grupo_id' => 'El grupo seleccionado no tiene un ciclo asociado.'])->withInput();
        }

        $cursoActual = CursoAcademico::active()->first();
        if (!$cursoActual) {
            return back()->withErrors(['grupo_id' => 'No hay un curso académico activo en este momento.'])->withInput();
        }

        // Verificar que el grupo seleccionado pertenece al alumno
        if (!$alumno->grupos->contains($validated['grupo_id'])) {
            return back()->withErrors(['grupo_id' => 'El grupo seleccionado no pertenece a tus grupos asignados.'])->withInput();
        }

        // Verificar máximo 300 palabras
        $wordCount = str_word_count($validated['descripcion']);
        if ($wordCount > 300) {
            return back()->withErrors(['descripcion' => "La descripción tiene {$wordCount} palabras. Máximo 300 permitidas."])
                ->withInput();
        }

        $proyecto = Proyecto::create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo_id,
            'curso_academico_id' => $cursoActual->id,
            'grupo_id' => $validated['grupo_id'],
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'enlace_repositorio' => $validated['enlace_repositorio'] ?? null,
            'enlace_despliegue' => $validated['enlace_despliegue'] ?? null,
        ]);

        // Subir imágenes
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $image) {
                $path = $this->optimizeAndStoreImage($image);
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
                $path = $this->optimizeAndStoreImage($image);
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
        $isAdmin = $user->hasRole(User::ROLE_ADMIN);

        if (!$isAdmin && !$user->hasRole(User::ROLE_PROFESOR)) {
            abort(403, 'No tienes permiso para calificar proyectos.');
        }

        if (!$isAdmin) {
            // Verificar que el profesor puede ver este proyecto (mismo grupo)
            $profesor = Profesor::where('user_id', $user->id)->first();
            if (!$profesor) {
                abort(403, 'No tienes perfil de profesor.');
            }

            // Grupos del profesor (tutor + equipo educativo)
            $grupoIds = $profesor->gruposTutor()->pluck('id')->toArray();
            $grupoIdsEdu = $profesor->gruposImpartidos()->pluck('grupos.id')->toArray();
            $todosGrupos = array_unique(array_merge($grupoIds, $grupoIdsEdu));

            $alumnoProyecto = Alumno::find($proyecto->alumno_id);
            if (!$alumnoProyecto || empty($todosGrupos) || empty(array_intersect($alumnoProyecto->grupos->pluck('id')->toArray(), $todosGrupos))) {
                abort(403, 'No tienes permiso para calificar este proyecto.');
            }
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

        // Notificar al alumno que su proyecto fue calificado
        $this->notificacionService->proyectoCalificado(
            $proyecto->alumno_id,
            $validated['calificacion']
        );

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
            ->where('es_destacado', true);

        // Filtros
        if ($request->ciclo) {
            $query->where('ciclo_id', $request->ciclo);
        }
        if ($request->curso) {
            $query->where('curso_academico_id', $request->curso);
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

        // Ciclos y cursos para los filtros
        $ciclos = Ciclo::all();
        $cursos = CursoAcademico::all();

        return view('proyectos.portfolio', compact(
            'proyectos',
            'proyectosAgrupados',
            'totalProyectos',
            'destacadosCount',
            'promedioCalificacion',
            'totalEmpresas',
            'ciclos',
            'cursos'
        ));
    }

    /**
     * Procesar solicitud de contacto de empresa.
     */
    public function enviarContacto(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'web' => 'nullable|url|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'contacto' => 'required|string|max:255',
        ]);

        \Illuminate\Support\Facades\Mail::to('icabrero@ieshlanz.es')
            ->send(new \App\Mail\ContactoEmpresaMail($validated));

        return back()->with('success', 'Tu solicitud ha sido enviada correctamente. Nos pondremos en contacto contigo pronto.');
    }

    /**
     * Redimensiona y optimiza una imagen antes de guardarla.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    protected function optimizeAndStoreImage($file): string
    {
        $tempPath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        // Obtener dimensiones originales
        list($width, $height) = getimagesize($tempPath);

        // Cargar imagen según formato usando GD
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $src = imagecreatefromjpeg($tempPath);
                break;
            case 'png':
                $src = imagecreatefrompng($tempPath);
                break;
            case 'gif':
                $src = imagecreatefromgif($tempPath);
                break;
            case 'webp':
                $src = imagecreatefromwebp($tempPath);
                break;
            default:
                // Si la extensión no es soportada por GD, guardamos directamente el original
                return $file->store('proyectos', 'public');
        }

        if (!$src) {
            return $file->store('proyectos', 'public');
        }

        // Definir dimensiones máximas (ancho/alto máximo de 1200px)
        $maxDim = 1200;
        if ($width > $maxDim || $height > $maxDim) {
            if ($width > $height) {
                $newWidth = $maxDim;
                $newHeight = (int) ($height * ($maxDim / $width));
            } else {
                $newHeight = $maxDim;
                $newWidth = (int) ($width * ($maxDim / $height));
            }

            $dst = imagecreatetruecolor($newWidth, $newHeight);

            // Preservar transparencia para PNG y WebP
            if ($extension === 'png' || $extension === 'webp') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($src);
            $src = $dst;
        }

        // Generar salida en buffer para usar Storage Facade
        ob_start();
        if ($extension === 'png') {
            imagepng($src, null, 8); // compresión PNG (0-9)
        } elseif ($extension === 'webp') {
            imagewebp($src, null, 80); // calidad WebP (0-100)
        } elseif ($extension === 'gif') {
            imagegif($src, null);
        } else {
            imagejpeg($src, null, 80); // calidad JPEG (0-100)
        }
        $imageData = ob_get_clean();
        imagedestroy($src);

        $fileName = uniqid() . '.' . ($extension === 'webp' || $extension === 'png' ? $extension : 'jpg');
        $path = 'proyectos/' . $fileName;

        Storage::disk('public')->put($path, $imageData);

        return $path;
    }
}