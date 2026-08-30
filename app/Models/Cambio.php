<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cambio extends Model
{
    use HasFactory;

    protected $fillable = [
        'registrable_type',
        'registrable_id',
        'accion',
        'campo',
        'antes',
        'despues',
        'descripcion',
        'usuario_id',
    ];

    protected $casts = [
        'antes' => 'array',
        'despues' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function registrable()
    {
        return $this->morphTo();
    }

    /**
     * Obtener los cambios de un modelo registrable
     */
    public static function paraRegistrable($registrable, $limit = 50)
    {
        return self::where('registrable_type', get_class($registrable))
            ->where('registrable_id', $registrable->getKey())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->with('usuario')
            ->get();
    }

    /**
     * Obtener todos los cambios con filtros
     */
    public static function conFiltros($usuarioId = null, $registrableType = null, $accion = null, $limit = 50)
    {
        $query = self::with('usuario', 'registrable')->orderBy('created_at', 'desc');

        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }

        if ($registrableType) {
            $query->where('registrable_type', $registrableType);
        }

        if ($accion) {
            $query->where('accion', $accion);
        }

        return $query->limit($limit)->get();
    }
}