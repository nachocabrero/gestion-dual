<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mensaje extends Model
{
    use HasFactory;

    protected $table = 'mensajes';

    protected $fillable = [
        'remitente_id',
        'asunto',
        'cuerpo',
        'tipo_destinatario',
        'resumen_destinatarios',
        'total_destinatarios',
    ];

    /**
     * Usuario remitente del mensaje.
     */
    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    /**
     * Usuarios destinatarios del mensaje con estado de lectura.
     */
    public function destinatarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mensaje_destinatarios')
            ->withPivot('leido_at')
            ->withTimestamps();
    }

    /**
     * Destinatarios que han leído el mensaje.
     */
    public function destinatariosLeidos(): BelongsToMany
    {
        return $this->destinatarios()->wherePivotNotNull('leido_at');
    }

    /**
     * Destinatarios pendientes de lectura.
     */
    public function destinatariosPendientes(): BelongsToMany
    {
        return $this->destinatarios()->wherePivotNull('leido_at');
    }

    /**
     * Comprobar si un usuario específico ya leyó el mensaje.
     */
    public function esLeidoPor(int $userId): bool
    {
        return $this->destinatarios()
            ->where('users.id', $userId)
            ->wherePivotNotNull('leido_at')
            ->exists();
    }

    /**
     * Marcar como leído para un usuario destinatario.
     */
    public function marcarLeidoPor(int $userId): void
    {
        $this->destinatarios()->updateExistingPivot($userId, [
            'leido_at' => now(),
        ]);
    }
}
