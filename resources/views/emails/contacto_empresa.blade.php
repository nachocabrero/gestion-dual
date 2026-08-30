<x-mail::message>
# Nueva Solicitud de Colaboración

Se ha recibido una nueva solicitud de colaboración de una empresa a través del portfolio público.

**Datos de la Empresa:**

- **Nombre:** {{ $datos['nombre'] }}
- **Dirección:** {{ $datos['direccion'] }}
- **Página Web:** {{ $datos['web'] ?? 'No indicada' }}
- **Email:** {{ $datos['email'] }}
- **Teléfono:** {{ $datos['telefono'] }}
- **Persona de Contacto:** {{ $datos['contacto'] }}

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
