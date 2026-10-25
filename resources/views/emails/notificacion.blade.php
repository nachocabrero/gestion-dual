<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Nueva notificación de Gestión DUAL H.Lanz' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    @php
        $logoSrc = asset('images/logo-ieshlanz.png');
        if (isset($message) && is_object($message) && method_exists($message, 'embed') && file_exists(public_path('images/logo-ieshlanz.png'))) {
            try {
                $logoSrc = $message->embed(public_path('images/logo-ieshlanz.png'));
            } catch (\Throwable $e) {
                $logoSrc = asset('images/logo-ieshlanz.png');
            }
        }
        $enlaceUrl = $enlace ?? (function_exists('route') ? route('notificaciones.index') : url('/notificaciones'));
    @endphp

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 32px 16px;">
        <tr>
            <td align="center">
                <!-- Main Card -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="padding: 28px 32px; background-color: #ffffff; text-align: center; border-bottom: 2px solid #0048FE;">
                            <img src="{{ $logoSrc }}" alt="IES Hermenegildo Lanz" style="max-width: 140px; height: auto; display: inline-block; margin-bottom: 12px;">
                            <div style="color: #0f172a; font-size: 18px; font-weight: 700; letter-spacing: -0.025em; margin: 0;">
                                Gestión DUAL H.Lanz
                            </div>
                            <div style="color: #64748b; font-size: 12px; margin-top: 4px; font-weight: 500;">
                                IES Hermenegildo Lanz · Formación Profesional
                            </div>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            
                            <!-- Notice Badge -->
                            <div style="margin-bottom: 20px;">
                                <span style="display: inline-block; padding: 6px 12px; border-radius: 9999px; background-color: #eff6ff; color: #0048FE; font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; border: 1px solid #dbeafe;">
                                    🔔 Nueva Notificación
                                </span>
                            </div>

                            <!-- Title -->
                            <h1 style="color: #0f172a; font-size: 20px; font-weight: 700; line-height: 1.35; margin: 0 0 12px 0;">
                                {{ $titulo }}
                            </h1>

                            @if(!empty($remitente))
                            <p style="color: #64748b; font-size: 13px; margin: 0 0 20px 0; font-weight: 500;">
                                Remitente: <strong style="color: #334155;">{{ $remitente }}</strong>
                            </p>
                            @endif

                            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 16px 0;">
                                Tienes una nueva notificación en la plataforma de <strong>Gestión DUAL H.Lanz</strong>:
                            </p>

                            <!-- Message Content Box -->
                            <div style="background-color: #f8fafc; border-left: 4px solid #0048FE; border-radius: 8px; padding: 18px 20px; margin: 0 0 28px 0; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                                <div style="color: #1e293b; font-size: 14px; line-height: 1.65; white-space: pre-line;">
                                    {!! nl2br(e($mensaje)) !!}
                                </div>
                            </div>

                            <!-- Action Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto 16px auto;">
                                <tr>
                                    <td align="center" style="border-radius: 10px; background-color: #0048FE;">
                                        <a href="{{ $enlaceUrl }}" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 72, 254, 0.25);">
                                            Ver notificación en la plataforma →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align: center; font-size: 12px; color: #94a3b8; margin: 0;">
                                Si el botón no funciona, puedes copiar y pegar el siguiente enlace en tu navegador:<br>
                                <a href="{{ $enlaceUrl }}" style="color: #0048FE; word-break: break-all;">{{ $enlaceUrl }}</a>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="color: #64748b; font-size: 12px; line-height: 1.5; margin: 0 0 8px 0;">
                                Este mensaje ha sido generado automáticamente por el sistema de Gestión DUAL del <strong>IES Hermenegildo Lanz</strong>.
                            </p>
                            <p style="color: #94a3b8; font-size: 11px; line-height: 1.5; margin: 0;">
                                Aviso de Privacidad y Confidencialidad (RGPD). La información remitida está destinada exclusivamente a los miembros de la comunidad educativa del centro escolar.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>