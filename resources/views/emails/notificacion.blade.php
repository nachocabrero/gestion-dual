<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #333;">{{ $titulo }}</h2>
    <img src="{{ asset('images/logo-ieshlanz.png') }}" alt="IES Hermenegildo Lanz" style="max-width: 150px; margin-bottom: 10px;">
    <p style="color: #666; line-height: 1.6;">{{ $mensaje }}</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #999; font-size: 12px;">IES Hermenegildo Lanz — Notificaciones</p>
</body>
</html>