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
    public function enviarEmail(int $usuarioId, string $titulo, string $mensaje): void
    {
        $usuario = User::find($usuarioId);
        if (!$usuario || !$usuario->email) {
            return;
        }

        // Usar mailable genérico
        Mail::send('emails.notificacion', [
            'titulo' => $titulo,
            'mensaje' => $mensaje,
        ], function ($message) use ($usuario, $titulo) {
            $message->to($usuario->email)
                    ->subject($titulo);
        });
    }

    /**
     * Notificación completa (email + in-app).
     */
    public function enviar(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?string $enlace = null, ?array $datos = null, ?int $duracionDias = null): void
    {
        $this->crear($usuarioId, $tipo, $titulo, $mensaje, $enlace, $datos, $duracionDias);
        $this->enviarEmail($usuarioId, $titulo, $mensaje);
    }

    /**
     * Notificar empresa asignada a un alumno.
     */
    public function empresaAsignada(int $alumnoId, string $nombreEmpresa): void
    {
        $alumno = \App\Models\Alumno::find($alumnoId);
        if (!$alumno) return;

        $this->enviar(
            $alumno->user_id,
            'empresa_asignada',
            'Empresa asignada: ' . $nombreEmpresa,
            'Se te ha asignado la empresa "' . $nombreEmpresa . '" para tus prácticas. Consulta los detalles en tu perfil.',
            route('alumnos.show', $alumno),
            ['empresa' => $nombreEmpresa],
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