<?php

namespace App\Console\Commands;

use App\Models\Alumno;
use App\Models\Cambio;
use App\Models\CursoAcademico;
use App\Models\Grupo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromocionAnual extends Command
{
    protected $signature = 'academico:promocion-anual
                            {--curso-destino= : Nombre del curso académico destino (ej: 2026-2027). Obligatorio.}
                            {--curso-origen= : Nombre del curso académico origen. Por defecto usa el activo.}
                            {--preview : Solo muestra el plan de promoción sin aplicar ningún cambio.}';

    protected $description = 'Promociona automáticamente a los alumnos al curso siguiente (1º → 2º) el 1 de agosto.';

    public function handle(): int
    {
        $destinoNombre = $this->option('curso-destino');
        if (!$destinoNombre) {
            $this->error('Es obligatorio indicar --curso-destino (ej: 2026-2027).');
            return self::FAILURE;
        }

        $origenNombre = $this->option('curso-origen');
        $preview = (bool) $this->option('preview');

        $destino = CursoAcademico::firstOrCreate(
            ['nombre' => $destinoNombre],
            [
                'fecha_inicio' => $this->inferirInicio($destinoNombre),
                'fecha_fin' => $this->inferirFin($destinoNombre),
                'is_active' => false,
            ]
        );

        $origen = $this->resolverOrigen($origenNombre);

        $this->line('');
        $this->info("=== Promoción anual de curso ===");
        $this->line("Curso origen : {$origen->nombre}");
        $this->line("Curso destino: {$destino->nombre}");
        if ($preview) {
            $this->warn('MODO PREVIEW — no se aplicará ningún cambio.');
        }
        $this->line('');

        // 1) Congelar el estado actual del curso origen en los grupos que no lo tienen
        if (!$preview) {
            $asalignar = Grupo::where(function ($q) use ($origen) {
                $q->whereNull('curso_academico_id')->orWhere('curso_academico_id', '!=', $origen->id);
            })->count();

            if ($asalignar > 0) {
                $this->line("Asignando curso origen {$origen->nombre} a $asalignar grupo(s) existente(s)...");
                DB::table('grupos')->update(['curso_academico_id' => $origen->id]);
            }

            // Backfill: cada pertenencia alumno-grupo hereda el curso de su grupo
            $backfilled = 0;
            foreach (DB::table('alumno_grupo')->whereNull('curso_academico_id')->get() as $fila) {
                $cursoId = DB::table('grupos')->where('id', $fila->grupo_id)->value('curso_academico_id');
                if ($cursoId) {
                    DB::table('alumno_grupo')
                        ->where('id', $fila->id)
                        ->update(['curso_academico_id' => $cursoId]);
                    $backfilled++;
                }
            }
            if ($backfilled > 0) {
                $this->line("Backfill de curso en $backfilled pertenencia(s) alumno-grupo.");
            }
        }

        // 2) Grupos de primer curso en el origen (incluye grupos aún sin curso asignado)
        $gruposOrigen = Grupo::where('numero', 1)
            ->where(function ($q) use ($origen) {
                $q->whereNull('curso_academico_id')
                    ->orWhere('curso_academico_id', $origen->id);
            })
            ->get();

        if ($gruposOrigen->isEmpty()) {
            $this->warn("No hay grupos de 1º en el curso origen {$origen->nombre}.");
            return self::SUCCESS;
        }

        $plan = [];
        foreach ($gruposOrigen as $grupoOrigen) {
            $alumnos = DB::table('alumno_grupo')
                ->where('grupo_id', $grupoOrigen->id)
                ->get();

            foreach ($alumnos as $fila) {
                // No volver a promocionar a quien ya tiene grupo en el curso destino
                $yaPromocionado = DB::table('alumno_grupo')
                    ->join('grupos', 'grupos.id', '=', 'alumno_grupo.grupo_id')
                    ->where('alumno_grupo.alumno_id', $fila->alumno_id)
                    ->where('alumno_grupo.curso_academico_id', $destino->id)
                    ->exists();

                if ($yaPromocionado) {
                    continue;
                }

                $plan[] = [
                    'alumno_id' => $fila->alumno_id,
                    'grupo_origen' => $grupoOrigen,
                    'numero_destino' => $grupoOrigen->numero + 1,
                ];
            }
        }

        $this->info("Alumnos que promocionan: " . count($plan));
        $this->line('');

        if ($this->output->isVerbose() || $preview) {
            foreach ($plan as $p) {
                $alumno = Alumno::find($p['alumno_id']);
                $nombre = $alumno?->user?->name ?? "#{$p['alumno_id']}";
                $this->line("  - {$nombre}  [{$p['grupo_origen']->numero}º → {$p['numero_destino']}º] {$p['grupo_origen']->linea->ciclo->codigo} {$p['grupo_origen']->linea->turno}");
            }
            $this->line('');
        }

        if ($preview) {
            $this->info('Ejecuta el comando sin --preview para aplicar la promoción.');
            return self::SUCCESS;
        }

        $this->line('Aplicando promoción...');
        $aplicados = 0;
        $creadosGrupos = 0;

        DB::transaction(function () use ($plan, $destino, $origen, &$aplicados, &$creadosGrupos) {
            foreach ($plan as $p) {
                $grupoOrigen = $p['grupo_origen'];

                // Buscar o crear el grupo homólogo en el curso destino (misma línea, número+1)
                $grupoDestino = Grupo::where('linea_id', $grupoOrigen->linea_id)
                    ->where('numero', $p['numero_destino'])
                    ->where('curso_academico_id', $destino->id)
                    ->first();

                if (!$grupoDestino) {
                    $ciclo = $grupoOrigen->linea->ciclo;
                    $turnoNombre = $grupoOrigen->linea->turno === 'tarde' ? 'Tarde' : 'Mañana';
                    $grupoDestino = Grupo::create([
                        'linea_id' => $grupoOrigen->linea_id,
                        'curso_academico_id' => $destino->id,
                        'numero' => $p['numero_destino'],
                        'nombre' => "{$p['numero_destino']}º {$ciclo->codigo} - {$turnoNombre}",
                        'tutor_id' => $grupoOrigen->tutor_id,
                        'is_active' => true,
                    ]);
                    $creadosGrupos++;
                }

                // Asignar al grupo del curso destino
                DB::table('alumno_grupo')->updateOrInsert(
                    ['alumno_id' => $p['alumno_id'], 'grupo_id' => $grupoDestino->id],
                    [
                        'curso_academico_id' => $destino->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Actualizar la matrícula por curso académico
                DB::table('alumno_ciclo_matricula')->updateOrInsert(
                    [
                        'alumno_id' => $p['alumno_id'],
                        'ciclo_id' => $grupoOrigen->linea->ciclo_id,
                        'curso_academico' => $destino->nombre,
                    ],
                    [
                        'matriculado_at' => $destino->fecha_inicio ?? now(),
                        'updated_at' => now(),
                    ]
                );

                $aplicados++;
            }
        });

        $this->info("Promoción aplicada: $aplicados alumno(s) promocionado(s), $creadosGrupos grupo(s) de destino creado(s).");

        $this->registrarEnHistorial($origen, $destino, $aplicados, $creadosGrupos);

        Log::info("Promoción anual: {$origen->nombre} → {$destino->nombre}, {$aplicados} alumnos, {$creadosGrupos} grupos.");

        return self::SUCCESS;
    }

    /**
     * Resuelve el curso origen: el indicado, el activo, o el inmediatamente anterior al destino.
     */
    private function resolverOrigen(?string $origenNombre): CursoAcademico
    {
        if ($origenNombre) {
            $origen = CursoAcademico::firstOrCreate(
                ['nombre' => $origenNombre],
                [
                    'fecha_inicio' => $this->inferirInicio($origenNombre),
                    'fecha_fin' => $this->inferirFin($origenNombre),
                    'is_active' => false,
                ]
            );
            return $origen;
        }

        $activo = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
        if ($activo) {
            return $activo;
        }

        return CursoAcademico::firstOrCreate(
            ['nombre' => '2025-2026'],
            ['fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'is_active' => false]
        );
    }

    private function registrarEnHistorial(CursoAcademico $origen, CursoAcademico $destino, int $aplicados, int $creadosGrupos): void
    {
        Cambio::create([
            'registrable_type' => CursoAcademico::class,
            'registrable_id' => $destino->id,
            'accion' => 'promocion_anual',
            'campo' => 'curso_academico',
            'antes' => ['curso' => $origen->nombre],
            'despues' => ['curso' => $destino->nombre, 'alumnos_promocionados' => $aplicados, 'grupos_creados' => $creadosGrupos],
            'descripcion' => "Promoción anual: {$origen->nombre} → {$destino->nombre} ({$aplicados} alumnos, {$creadosGrupos} grupos)",
            'usuario_id' => null,
        ]);
    }

    private function inferirInicio(string $nombre): string
    {
        $anio = (int) substr($nombre, 0, 4);
        return $anio ? "{$anio}-09-01" : now()->format('Y-m-d');
    }

    private function inferirFin(string $nombre): string
    {
        $anioBase = (int) substr($nombre, 0, 4);
        return $anioBase ? ($anioBase + 1) . '-06-30' : now()->addYear()->format('Y-m-d');
    }
}
