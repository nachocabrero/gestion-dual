<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoImagen extends Model
{
    use HasFactory;

    protected $table = 'proyecto_imagenes';

    protected $fillable = [
        'proyecto_id',
        'url',
    ];

    /**
     * Proyecto asociado.
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }
}