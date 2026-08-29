@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $oferta->especialidad_requerida }}</h1>
    <p>{{ $oferta->descripcion ?? '' }}</p>
    <p>Alumnos: {{ $oferta->num_alumnos }}</p>
    <span class="badge bg-{{ $oferta->estado === 'activa' ? 'success' : ($oferta->estado === 'pendiente' ? 'warning' : 'secondary') }}">
        {{ ucfirst($oferta->estado) }}
    </span>

    @if($oferta->solicitudes->count() > 0)
    <h3 class="mt-4">Solicitudes</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($oferta->solicitudes as $solicitud)
            <tr>
                <td>{{ $solicitud->alumno->nombre }}</td>
                <td>{{ ucfirst($solicitud->estado) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection