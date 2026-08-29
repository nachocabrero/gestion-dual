<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesor extends Model
{
    use HasFactory;

    protected $table = 'profesores';

    protected $fillable = [
        'user_id',
        'especialidad',
        'es_tutor',
        'es_coordinador_dual',
    ];

    protected $casts = [
        'es_tutor' => 'boolean',
        'es_coordinador_dual' => 'boolean',
    ];

    /**
     * Usuario asociado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Grupos cuyo tutor es este profesor.
     */
    public function gruposTutor(): HasMany
    {
        return $this->hasMany(Grupo::class, 'tutor_id', 'user_id');
    }

    /**
     * Asignaturas que imparte.
     */
    public function asignaturas(): BelongsToMany
    {
        return $this->belongsToMany(Asignatura::class, 'profesor_asignatura');
    }

    /**
     * Equipos educativos (grupos a los que pertenece).
     */
    public function equiposEducativos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'profesor_grupo', 'profesor_id', 'grupo_id')
            ->select('grupos.*');
    }

    /**
     * Sustituciones donde este profesor es el sustituto.
     */
    public function sustituciones(): HasMany
    {
        return $this->hasMany(Sustitucion::class, 'profesor_sustituto_id');
    }

    /**
     * Scopes
     */
    public function scopeTutores($query)
    {
        return $query->where('es_tutor', true);
    }

    public function scopeCoordinadores($query)
    {
        return $query->where('es_coordinador_dual', true);
    }
}