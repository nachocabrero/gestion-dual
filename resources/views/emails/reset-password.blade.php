<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña · Gestión DUAL H.Lanz</title>
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
    @endphp

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 32px 16px;">
        <tr>
            <td align="center">
                <!-- Main Card -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    
                    <!-- Header Corporativo -->
                    <tr>
                        <td style="padding: 28px 32px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); text-align: center; border-bottom: 3px solid #0048FE;">
                            <img src="{{ $logoSrc }}" alt="IES Hermenegildo Lanz" style="max-width: 130px; height: auto; display: inline-block; margin-bottom: 12px;">
                            <div style="color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: -0.025em; margin: 0;">
                                Gestión DUAL H.Lanz
                            </div>
                            <div style="color: #94a3b8; font-size: 12px; margin-top: 4px; font-weight: 500;">
                                IES Hermenegildo Lanz · Portal Académico y Formación Profesional
                            </div>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            
                            <!-- Notice Badge -->
                            <div style="margin-bottom: 20px;">
                                <span style="display: inline-block; padding: 6px 12px; border-radius: 9999px; background-color: #fef3c7; color: #b45309; font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; border: 1px solid #fde68a;">
                                    🔒 Restablecimiento de Acceso
                                </span>
                            </div>

                            <!-- Title -->
                            <h1 style="color: #0f172a; font-size: 20px; font-weight: 700; line-height: 1.35; margin: 0 0 12px 0;">
                                Solicitud de restablecimiento de contraseña
                            </h1>

                            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola, <strong>{{ $user->name ?? 'Usuario' }}</strong>.
                            </p>

                            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 24px 0;">
                                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en la plataforma de <strong>Gestión DUAL del IES Hermenegildo Lanz</strong>.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
                                <tr>
                                    <td align="center" style="border-radius: 12px; background-color: #0048FE;">
                                        <a href="{{ $url }}" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 12px; letter-spacing: -0.01em;">
                                            Restablecer mi contraseña →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #64748b; font-size: 13px; line-height: 1.5; margin: 0 0 16px 0;">
                                Este enlace de recuperación expirará en <strong>60 minutos</strong> por motivos de seguridad.
                            </p>

                            <p style="color: #64748b; font-size: 13px; line-height: 1.5; margin: 0 0 24px 0;">
                                Si no has realizado esta solicitud, puedes ignorar este mensaje de forma segura; tu contraseña actual no será modificada.
                            </p>

                            <!-- Fallback Link Box -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-top: 24px;">
                                <p style="color: #64748b; font-size: 11px; line-height: 1.4; margin: 0 0 6px 0;">
                                    Si tienes problemas con el botón, copia y pega la siguiente URL en tu navegador web:
                                </p>
                                <a href="{{ $url }}" style="color: #0048FE; font-size: 11px; word-break: break-all; text-decoration: underline;">
                                    {{ $url }}
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="color: #94a3b8; font-size: 11px; line-height: 1.5; margin: 0 0 8px 0;">
                                Este es un correo automático generado por el sistema de <strong>Gestión DUAL del IES Hermenegildo Lanz</strong>.
                            </p>
                            <p style="color: #94a3b8; font-size: 11px; line-height: 1.5; margin: 0;">
                                © {{ date('Y') }} IES Hermenegildo Lanz · Conforme al Reglamento General de Protección de Datos (RGPD)
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
