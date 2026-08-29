@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ofertas de Prácticas</h1>

    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="especialidad" class="form-control">
                    <option value="">Todas las especialidades</option>
                    @foreach($especialidades as $esp)
                        <option value="{{ $esp }}" {{ request('especialidad') == $esp ? 'selected' : '' }}>{{ $esp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    @foreach($ofertas as $oferta)
    <div class="card mb-2">
        <div class="card-body">
            <h5>{{ $oferta->especialidad_requerida }}</h5>
            <p>{{ $oferta->descripcion ?? '' }}</p>
            <span class="badge bg-{{ $oferta->estado === 'activa' ? 'success' : ($oferta->estado === 'pendiente' ? 'warning' : 'secondary') }}">
                {{ ucfirst($oferta->estado) }}
            </span>
            <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-sm btn-outline-primary">Ver</a>
        </div>
    </div>
    @endforeach

    {{ $ofertas->links() }}
</div>
@endsection