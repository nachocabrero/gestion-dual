<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificacionService
{
    /**
     * Crear una notificación in-app.
     */
    public function crear(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?string $enlace = null, ?array $datos = null, ?int $duracionDias = null): Notificacion
    {
        $expiraEn = null;
        if ($duracionDias !== null && $duracionDias > 0) {
            $expiraEn = now()->addDays($duracionDias);
        }

        return Notificacion::create([
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'enlace' => $enlace,
            'datos' => $datos,
            'expira_en' => $expiraEn,
        ]);
    }

    /**
     * Enviar notificación por email.
     */
    public function enviarEmail(
        int $usuarioId,
        string $titulo,
        string $mensaje,
        ?string $enlace = null,
        ?string $remitente = null,
        ?string $asuntoPersonalizado = null,
        ?string $replyToEmail = null,
        ?string $replyToNombre = null,
        ?array $contactoRemitente = null,
        bool $esParaEmpresa = false
    ): void
    {
        $usuario = User::find($usuarioId);
        if (!$usuario || !$usuario->email) {
            return;
        }

        $esEmpresaDestinataria = $esParaEmpresa || $usuario->hasRole(User::ROLE_EMPRESA);

        if ($esEmpresaDestinataria) {
            if (empty($replyToEmail) && !empty($contactoRemitente['email'])) {
                $replyToEmail = $contactoRemitente['email'];
            }
            if (empty($replyToNombre) && !empty($contactoRemitente['nombre'])) {
                $replyToNombre = $contactoRemitente['nombre'];
            }
            if ($contactoRemitente === null && (!empty($replyToEmail) || !empty($remitente))) {
                $contactoRemitente = [
                    'nombre' => $replyToNombre ?? $remitente ?? 'Profesorado IES Hermenegildo Lanz',
                    'email' => $replyToEmail ?? '',
                    'telefono' => '958 89 38 50',
                ];
            }
        }

        try {
            Mail::to($usuario->email)->send(new \App\Mail\NotificacionMailable(
                titulo: $titulo,
                mensaje: $mensaje,
                enlace: $enlace,
                remitente: $remitente,
                asuntoPersonalizado: $asuntoPersonalizado,
                replyToEmail: $replyToEmail,
                replyToNombre: $replyToNombre,
                contactoRemitente: $contactoRemitente,
                esParaEmpresa: $esEmpresaDestinataria
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error enviando email de notificación a usuario {$usuarioId}: " . $e->getMessage());
        }
    }

    /**
     * Notificación completa (email + in-app).
     */
    public function enviar(
        int $usuarioId,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?string $enlace = null,
        ?array $datos = null,
        ?int $duracionDias = null,
        ?string $remitente = null,
        ?string $asuntoEmail = null,
        ?string $replyToEmail = null,
        ?string $replyToNombre = null,
        ?array $contactoRemitente = null,
        bool $esParaEmpresa = false
    ): void
    {
        $this->crear($usuarioId, $tipo, $titulo, $mensaje, $enlace, $datos, $duracionDias);
        $this->enviarEmail(
            usuarioId: $usuarioId,
            titulo: $titulo,
            mensaje: $mensaje,
            enlace: $enlace,
            remitente: $remitente,
            asuntoPersonalizado: $asuntoEmail,
            replyToEmail: $replyToEmail,
            replyToNombre: $replyToNombre,
            contactoRemitente: $contactoRemitente,
            esParaEmpresa: $esParaEmpresa
        );
    }

    /**
     * Notificar empresa asignada a un alumno.
     */
    public function empresaAsignada(int $alumnoId, string $nombreEmpresa): void
    {
        $alumno = \App\Models\Alumno::find($alumnoId);
        if (!$alumno || !$alumno->user_id) return;

        $this->enviar(
            $alumno->user_id,
            'empresa_asignada',
            'Empresa asignada: ' . $nombreEmpresa,
            'Se ha registrado oficialmente tu asignación a la empresa "' . $nombreEmpresa . '" para tus prácticas. Consulta los detalles en tu perfil.',
            route('alumnos.show', $alumno),
            ['empresa' => $nombreEmpresa],
            90
        );
    }

    /**
     * Notificar inclusión de un alumno como candidato en una oferta de prácticas.
     * Deja claro que es una fase de candidatura/selección y no una práctica garantizada.
     */
    public function candidaturaOferta(\App\Models\Alumno $alumno, \App\Models\OfertaPractica $oferta): void
    {
        if (!$alumno->user_id) {
            return;
        }

        $oferta->loadMissing('empresa');
        $empresaNombre = $oferta->empresa?->nombre ?: 'la empresa colaboradora';
        $especialidad = $oferta->especialidad_requerida ?: 'Prácticas';

        $titulo = 'Candidatura a oferta de prácticas: ' . $empresaNombre . ' (' . $especialidad . ')';
        $mensaje = "Has sido incluido como candidato para la oferta de prácticas en {$empresaNombre} ({$especialidad}).\n\nTu perfil y currículum se encuentran en fase de revisión y selección por parte del centro y la empresa.\n\nImportante: Esta comunicación no supone la asignación definitiva de las prácticas. Te informaremos cuando el proceso de selección concluya y se formalice la práctica oficial.";

        $this->enviar(
            $alumno->user_id,
            'candidatura_oferta',
            $titulo,
            $mensaje,
            route('ofertas.show', $oferta),
            [
                'oferta_id' => $oferta->id,
                'empresa' => $empresaNombre,
                'especialidad' => $especialidad,
            ],
            60
        );
    }

    /**
     * Notificar formalización oficial de una práctica al alumno (creada en Gestión de Prácticas).
     */
    public function practicaFormalizada(\App\Models\Practica $practica): void
    {
        $practica->loadMissing(['alumno.user', 'empresa', 'tutorLaboral']);
        $alumno = $practica->alumno;
        if (!$alumno || !$alumno->user_id) {
            return;
        }

        $empresaNombre = $practica->empresa?->nombre ?: 'Empresa colaboradora';
        $titulo = 'Práctica formalizada: ' . $empresaNombre;
        $mensaje = "Se ha registrado y formalizado oficialmente tu práctica en \"{$empresaNombre}\".\n\nPuedes consultar los detalles de la asignación, fechas y tutor laboral en tu perfil de la plataforma.";

        $this->enviar(
            $alumno->user_id,
            'practica_formalizada',
            $titulo,
            $mensaje,
            route('alumnos.show', $alumno),
            [
                'practica_id' => $practica->id,
                'empresa' => $empresaNombre,
            ],
            90
        );
    }

    /**
     * Notificar cambio de estado del acuerdo.
     */
    public function acuerdoCambiado(int $alumnoId, string $nuevoEstado): void
    {
        $alumno = \App\Models\Alumno::find($alumnoId);
        if (!$alumno) return;

        $estados = [
            'pendiente' => 'Pendiente de firma',
            'firmado' => 'Firmado',
            'rechazado' => 'Rechazado',
            'expirado' => 'Expirado',
        ];

        $this->enviar(
            $alumno->user_id,
            'estado_acuerdo',
            'Estado del acuerdo actualizado',
            'El estado del acuerdo de prácticas ha cambiado a: ' . ($estados[$nuevoEstado] ?? $nuevoEstado),
            route('alumnos.show', $alumno),
            ['estado' => $nuevoEstado],
            60
        );
    }

    /**
     * Notificar proyecto calificado.
     */
    public function proyectoCalificado(int $alumnoId, float $nota): void
    {
        $alumno = \App\Models\Alumno::find($alumnoId);
        if (!$alumno) return;

        $this->enviar(
            $alumno->user_id,
            'proyecto_calificado',
            'Tu proyecto ha sido calificado',
            'Se ha calificado tu proyecto con una nota de ' . number_format($nota, 2) . '/10.',
            route('alumnos.show', $alumno),
            ['nota' => $nota],
            30
        );
    }

    /**
     * Notificar a un profesor sobre un alumno.
     */
    public function alumnoAsignado(int $profesorId, string $nombreAlumno, string $grupo): void
    {
        $this->enviar(
            $profesorId,
            'alumno_asignado',
            'Alumno asignado a tu grupo',
            'Se ha asignado al alumno "' . $nombreAlumno . '" a tu grupo ' . $grupo . '.',
            null,
            ['alumno' => $nombreAlumno, 'grupo' => $grupo],
            30
        );
    }

    /**
     * Notificar a un alumno que hay una nueva oferta de prácticas dirigida a su grupo.
     */
    public function ofertaEnviada(\App\Models\Alumno $alumno, \App\Models\OfertaPractica $oferta): void
    {
        if (!$alumno->user_id) {
            return;
        }

        $this->enviar(
            $alumno->user_id,
            'oferta_nueva',
            'Nueva oferta de prácticas: ' . $oferta->especialidad_requerida,
            'Se ha publicado una nueva oferta de prácticas para tu grupo (' . $oferta->especialidad_requerida . '). Puedes postularte desde el detalle de la oferta.',
            route('ofertas.show', $oferta),
            ['oferta_id' => $oferta->id],
            30
        );
    }

    /**
     * Notificar a los alumnos implicados cuando una oferta de prácticas cambia de estado a Cerrada.
     */
    public function ofertaCerrada(\App\Models\OfertaPractica $oferta): void
    {
        $oferta->loadMissing(['solicitudes.alumno.user', 'practicas.alumno.user', 'empresa']);

        $alumnosSolicitudes = $oferta->solicitudes
            ->whereIn('estado', ['aceptado', 'pendiente'])
            ->pluck('alumno')
            ->filter();

        $alumnosPracticas = $oferta->practicas
            ->pluck('alumno')
            ->filter();

        $alumnos = $alumnosSolicitudes->concat($alumnosPracticas)->unique('id');

        $empresaNombre = $oferta->empresa?->nombre ?: 'la empresa colaboradora';
        $especialidad = $oferta->especialidad_requerida ?: 'Prácticas';

        foreach ($alumnos as $alumno) {
            if (!$alumno->user_id) {
                continue;
            }

            $this->enviar(
                $alumno->user_id,
                'oferta_cerrada',
                'Oferta de prácticas cerrada: ' . $especialidad,
                'La oferta de prácticas de ' . $empresaNombre . ' (' . $especialidad . ') ha cambiado su estado a Cerrada.',
                route('ofertas.show', $oferta),
                [
                    'oferta_id' => $oferta->id,
                    'empresa' => $empresaNombre,
                ],
                60
            );
        }
    }

    /**
     * Marcar como leídas todas las notificaciones de un usuario.
     */
    public function marcarTodasLeidas(int $usuarioId): int
    {
        return Notificacion::marcarTodasLeidas($usuarioId);
    }

    /**
     * Obtener notificaciones no leídas de un usuario.
     */
    public function obtenerNoLeidas(int $usuarioId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Notificacion::where('usuario_id', $usuarioId)
            ->where('es_leida', false)
            ->where(function ($q) {
                $q->whereNull('expira_en')
                  ->orWhere('expira_en', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Contar no leídas.
     */
    public function contarNoLeidas(int $usuarioId): int
    {
        return Notificacion::contarNoLeidas($usuarioId);
    }

    /**
     * Limpiar notificaciones expiradas (tarea programada).
     */
    public function limpiarExpiradas(): int
    {
        return Notificacion::limpiarExpiradas();
    }
}