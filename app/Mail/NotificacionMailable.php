<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $titulo,
        public string $mensaje,
        public ?string $enlace = null,
        public ?string $remitente = null,
        public ?string $asuntoPersonalizado = null,
        public ?string $replyToEmail = null,
        public ?string $replyToNombre = null,
        public ?array $contactoRemitente = null,
        public bool $esParaEmpresa = false
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyTo = [];
        if (!empty($this->replyToEmail)) {
            $replyTo[] = new Address($this->replyToEmail, $this->replyToNombre ?? $this->remitente ?? '');
        }

        return new Envelope(
            subject: $this->asuntoPersonalizado ?? ('Nueva notificación de Gestión DUAL H.Lanz: ' . $this->titulo),
            replyTo: $replyTo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notificacion',
            with: [
                'titulo' => $this->titulo,
                'mensaje' => $this->mensaje,
                'enlace' => $this->enlace,
                'remitente' => $this->remitente,
                'replyToEmail' => $this->replyToEmail,
                'replyToNombre' => $this->replyToNombre,
                'contactoRemitente' => $this->contactoRemitente,
                'esParaEmpresa' => $this->esParaEmpresa,
            ],
        );
    }
}
