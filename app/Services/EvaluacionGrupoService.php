<?php

namespace App\Services;

use App\Models\Anotacion;
use App\Models\Grupo;
use Illuminate\Support\Collection;

class EvaluacionGrupoService
{
    /**
     * Calcula las posiciones y datos de evaluación para un conjunto de alumnos de un grupo.
     *
     * @param Grupo $grupo
     * @param Collection|null $alumnos Colección de alumnos (si es null, toma los alumnos del grupo)
     * @return array{
     *   tiene_evaluaciones: bool,
     *   total_evaluaciones: int,
     *   ranking_alumnos: Collection,
     *   todas_observaciones: Collection
     * }
     */
    public function calcularEvaluacionesGrupo(Grupo $grupo, ?Collection $alumnos = null): array
    {
        if ($alumnos === null) {
            $alumnos = $grupo->alumnos()->with('user')->get();
        }

        if ($alumnos->isEmpty()) {
            return [
                'tiene_evaluaciones' => false,
                'total_evaluaciones' => 0,
                'ranking_alumnos' => collect(),
                'todas_observaciones' => collect(),
            ];
        }

        $alumnoIds = $alumnos->pluck('id')->filter()->values()->toArray();

        // Cargar todas las anotaciones de estos alumnos con profesor y usuario en una sola consulta
        $anotaciones = Anotacion::whereIn('alumno_id', $alumnoIds)
            ->with(['profesor.user', 'alumno.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $anotacionesPorAlumno = $anotaciones->groupBy('alumno_id');

        $alumnosConMedia = [];
        $alumnosSinMedia = [];

        foreach ($alumnos as $alumno) {
            $anots = $anotacionesPorAlumno->get($alumno->id, collect());
            $puestos = $anots->whereNotNull('puesto')->pluck('puesto');
            $media = $puestos->isNotEmpty() ? round($puestos->avg(), 2) : null;

            $item = [
                'alumno_id' => $alumno->id,
                'alumno' => $alumno,
                'nombre' => $alumno->user?->name ?? '',
                'media_puesto' => $media,
                'total_observaciones' => $anots->count(),
                'observaciones' => $anots->map(fn($a) => [
                    'id' => $a->id,
                    'profesor_id' => $a->profesor_id,
                    'profesor_nombre' => $a->profesor?->user?->name ?? 'Profesor',
                    'puesto' => $a->puesto,
                    'titulo' => $a->titulo,
                    'contenido' => $a->contenido,
                    'fecha' => $a->created_at?->format('d/m/Y H:i'),
                ])->values(),
            ];

            if ($media !== null) {
                $alumnosConMedia[] = $item;
            } else {
                $alumnosSinMedia[] = $item;
            }
        }

        $tieneEvaluaciones = !empty($alumnosConMedia);

        // Si tiene evaluaciones, ordenar a los que tienen media por media ascendente (menor puesto = mejor)
        // En caso de empate en media, ordenar por nombre alfabético
        usort($alumnosConMedia, function ($a, $b) {
            if ($a['media_puesto'] != $b['media_puesto']) {
                return $a['media_puesto'] <=> $b['media_puesto'];
            }
            return \App\Support\StrSort::compare($a['nombre'], $b['nombre']);
        });

        // Asignar posición secuencial: 1 (#1 mejor alumno), 2 (#2 segundo mejor), etc.
        $posicion = 1;
        $ranking = [];
        foreach ($alumnosConMedia as $item) {
            $item['posicion'] = $posicion++;
            $ranking[$item['alumno_id']] = $item;
        }

        foreach ($alumnosSinMedia as $item) {
            $item['posicion'] = null;
            $ranking[$item['alumno_id']] = $item;
        }

        return [
            'tiene_evaluaciones' => $tieneEvaluaciones,
            'total_evaluaciones' => count($alumnosConMedia),
            'ranking_alumnos' => collect($ranking),
            'todas_observaciones' => $anotaciones,
        ];
    }

    /**
     * Ordena una colección de items según el criterio especificado.
     *
     * @param array|Collection $items
     * @param string $criterio 'nombre' o 'evaluacion'
     * @return array
     */
    public function ordenarLista(array|Collection $items, string $criterio = 'evaluacion'): array
    {
        $array = is_array($items) ? $items : $items->values()->all();

        usort($array, function ($a, $b) use ($criterio) {
            $nombreA = is_array($a) ? ($a['nombre'] ?? $a['alumno']->user->name ?? '') : ($a->user->name ?? '');
            $nombreB = is_array($b) ? ($b['nombre'] ?? $b['alumno']->user->name ?? '') : ($b->user->name ?? '');

            if ($criterio === 'nombre') {
                return \App\Support\StrSort::compare($nombreA, $nombreB);
            }

            // Orden por evaluación
            $posA = is_array($a) ? ($a['posicion'] ?? null) : null;
            $posB = is_array($b) ? ($b['posicion'] ?? null) : null;

            if ($posA !== null && $posB !== null) {
                if ($posA !== $posB) {
                    return $posA <=> $posB;
                }
                return \App\Support\StrSort::compare($nombreA, $nombreB);
            }

            if ($posA !== null && $posB === null) {
                return -1;
            }

            if ($posA === null && $posB !== null) {
                return 1;
            }

            return \App\Support\StrSort::compare($nombreA, $nombreB);
        });

        return $array;
    }
}
