@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mis Solicitudes de Prácticas</h1>

    @foreach($solicitudes as $solicitud)
    <div class="card mb-2">
        <div class="card-body">
            <h5>{{ $solicitud->oferta->especialidad_requerida }}</h5>
            <span class="badge bg-{{ $solicitud->estado === 'aceptado' ? 'success' : ($solicitud->estado === 'pendiente' ? 'warning' : 'secondary') }}">
                {{ ucfirst($solicitud->estado) }}
            </span>
        </div>
    </div>
    @endforeach

    {{ $solicitudes->links() }}
</div>
@endsection