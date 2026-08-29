<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'titulo',
        'mensaje',
        'datos',
        'enlace',
        'es_leida',
        'expira_en',
    ];

    protected $casts = [
        'datos' => 'array',
        'es_leida' => 'boolean',
        'expira_en' => 'datetime',
    ];

    /**
     * Usuario que recibe la notificación.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Notificaciones no leídas de un usuario.
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('es_leida', false)
                     ->where(function ($q) {
                         $q->whereNull('expira_en')
                           ->orWhere('expira_en', '>', now());
                     });
    }

    /**
     * Marcar como leída.
     */
    public function marcarLeida(): void
    {
        $this->update(['es_leida' => true]);
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas.
     */
    public static function marcarTodasLeidas(int $usuarioId): int
    {
        return self::where('usuario_id', $usuarioId)
            ->where('es_leida', false)
            ->update(['es_leida' => true]);
    }

    /**
     * Contar no leídas de un usuario.
     */
    public static function contarNoLeidas(int $usuarioId): int
    {
        return self::where('usuario_id', $usuarioId)
            ->where('es_leida', false)
            ->where(function ($q) {
                $q->whereNull('expira_en')
                  ->orWhere('expira_en', '>', now());
            })
            ->count();
    }

    /**
     * Limpiar notificaciones expiradas.
     */
    public static function limpiarExpiradas(): int
    {
        return self::whereNotNull('expira_en')
            ->where('expira_en', '<', now())
            ->delete();
    }
}