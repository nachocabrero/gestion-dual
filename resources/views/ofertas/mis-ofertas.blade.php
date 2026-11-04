@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mis Solicitudes de Prácticas</h1>

    @foreach($solicitudes as $solicitud)
    <div class="card mb-2">
        <div class="card-body">
            <h5>{{ $solicitud->oferta->especialidad_requerida }}</h5>
            @if($solicitud->estado === 'aceptado')
                <span class="badge bg-primary">Candidato preseleccionado</span>
            @elseif($solicitud->estado === 'pendiente')
                <span class="badge bg-warning text-dark">Pendiente de revisión</span>
            @elseif($solicitud->estado === 'rechazado')
                <span class="badge bg-danger">No seleccionado</span>
            @elseif($solicitud->estado === 'retirado')
                <span class="badge bg-secondary">Retirado</span>
            @else
                <span class="badge bg-secondary">{{ ucfirst($solicitud->estado) }}</span>
            @endif
        </div>
    </div>
    @endforeach

    {{ $solicitudes->links() }}
</div>
@endsection