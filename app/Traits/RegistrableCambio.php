<?php

namespace App\Traits;

use App\Models\Cambio;
use Illuminate\Support\Facades\Auth;

trait RegistrableCambio
{
    /**
     * Interceptar cambios y registrarlos
     */
    protected static function bootRegistrableCambio()
    {
        static::created(function ($model) {
            self::registrarCambio($model, 'created', descripcion: 'Registro creado');
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();

            if (empty($changes)) {
                return;
            }

            // Si cambió el estado, registrar como especial
            if (isset($changes['estado'])) {
                $antes = $original['estado'] ?? null;
                $despues = $changes['estado'];
                self::registrarCambio(
                    $model,
                    'estado_cambiado',
                    campo: 'estado',
                    antes: ['estado' => $antes],
                    despues: ['estado' => $despues],
                    descripcion: "Estado cambiado: {$antes} → {$despues}"
                );
                return;
            }

            // Cambios genéricos por campo
            foreach ($changes as $campo => $nuevoValor) {
                $valorAnterior = $original[$campo] ?? null;
                self::registrarCambio(
                    $model,
                    'updated',
                    campo: $campo,
                    antes: [$campo => $valorAnterior],
                    despues: [$campo => $nuevoValor],
                    descripcion: "{$campo} cambiado: " . var_export($valorAnterior, true) . " → " . var_export($nuevoValor, true)
                );
            }
        });

        static::deleted(function ($model) {
            self::registrarCambio(
                $model,
                'deleted',
                descripcion: 'Registro eliminado',
                despues: $model->toArray()
            );
        });
    }

    /**
     * Registrar un cambio manualmente
     */
    public static function registrarCambio($registrable, string $accion, string $campo = null, array $antes = null, array $despues = null, string $descripcion = null)
    {
        Cambio::create([
            'registrable_type' => get_class($registrable),
            'registrable_id' => $registrable->getKey(),
            'accion' => $accion,
            'campo' => $campo,
            'antes' => $antes,
            'despues' => $despues,
            'descripcion' => $descripcion,
            'usuario_id' => Auth::id(),
        ]);
    }

    /**
     * Obtener los cambios de este modelo
     */
    public function cambios()
    {
        return $this->morphMany(Cambio::class, 'registrable')
            ->orderBy('created_at', 'desc');
    }
}