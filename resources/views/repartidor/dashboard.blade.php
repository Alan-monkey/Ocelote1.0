@extends('layouts.app')
@section('content')

<style>
:root 
{ 
    --azul_1:#457b9d; 
    --azul_2:#132d46; 
    --azul_3:#a8dadc; 
    --blanco:#f1faee; 
    --verde_azul:#07cdaf; 
}
.dashboard-container 
{ 
    min-height:100vh; 
    background:linear-gradient(145deg,#e8f4f8,#d0eaf0); 
    padding:30px 0; 
    font-family:'Poppins','Segoe UI',sans-serif; 
}
.welcome-card 
{ 
    background:linear-gradient(135deg,var(--azul_2),var(--azul_1)); 
    color:white; 
    border-radius:24px; 
    padding:30px; 
    margin-bottom:30px; 
    box-shadow:0 10px 30px rgba(19,45,70,.2); 
}
.welcome-card h2 
{ 
    margin:0 0 8px; 
    font-weight:700; 
}
.welcome-card p 
{ 
    margin:0; 
    opacity:.9; 
    font-size:1.05rem; 
}
.ruta-card 
{ 
    background:white; 
    border-radius:20px; 
    padding:24px; 
    margin-bottom:20px; 
    box-shadow:0 8px 20px rgba(19,45,70,.08); 
    transition:all .3s; 
    border:3px solid transparent; 
}
.ruta-card.hoy 
{ 
    border-color:var(--verde_azul); 
    box-shadow:0 12px 30px rgba(7,205,175,.2); 
}
.ruta-card:hover 
{ 
    transform:translateY(-4px); 
    box-shadow:0 12px 30px rgba(19,45,70,.12); 
}
.badge-dia 
{ 
    background:var(--azul_3); 
    color:var(--azul_2); 
    padding:6px 16px; 
    border-radius:20px; 
    font-size:.85rem; 
    font-weight:700; 
    display:inline-block; 
}
.badge-hoy 
{ 
    background:var(--verde_azul); 
    color:white; 
    padding:6px 16px; 
    border-radius:20px; 
    font-size:.85rem; 
    font-weight:700; 
    display:inline-block; 
    animation:pulse 2s infinite; 
}
@keyframes pulse 
{ 0%,100%{transform:scale(1);} 
50%{transform:scale(1.05);} 
}
.btn-iniciar 
{ 
    background:linear-gradient(135deg,var(--verde_azul),var(--azul_1)); 
    color:white; border:none; border-radius:12px; 
    padding:12px 28px; 
    font-weight:700; 
    font-size:1.05rem; 
    cursor:pointer; 
    transition:all .3s; 
    box-shadow:0 4px 15px rgba(7,205,175,.3); 
}
.btn-iniciar:hover 
{ 
    transform:translateY(-2px); 
    box-shadow:0 6px 20px rgba(7,205,175,.5); 
}
.clientes-preview 
{ 
    display:flex; 
    flex-wrap:wrap; 
    gap:8px; 
    margin-top:12px; 
}
.cliente-mini 
{ 
    background:#f0f8fb; 
    padding:6px 12px; 
    border-radius:10px; 
    font-size:.85rem; 
    color:var(--azul_2); 
    font-weight:600; 
}
.sin-rutas 
{ 
    text-align:center; 
    padding:60px 20px; 
}
.sin-rutas i 
{ 
    font-size:4rem; 
    color:var(--azul_3); 
    opacity:.5; 
    margin-bottom:20px; 
}
</style>

<div class="dashboard-container">
<div class="container">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="welcome-card">
        <h2><i class="fas fa-truck me-2"></i>Bienvenido, {{ $user->nombre }}</h2>
        <p>Hoy es <strong>{{ $diaActual }}</strong> — Aquí están tus rutas asignadas</p>
    </div>

    @forelse($rutas as $ruta)
        @php
            $esHoy = $ruta->dia_reparto === $diaActual;
            $asignacionRaw = $ruta->asignacion_activa ?? null;
            $asignacion = is_array($asignacionRaw) ? $asignacionRaw : (is_object($asignacionRaw) ? (array)$asignacionRaw : null);
            $tieneAsignacion = $asignacion !== null;
        @endphp

        <div class="ruta-card {{ $esHoy ? 'hoy' : '' }}">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <h4 class="mb-2" style="color:var(--azul_2); font-weight:700;">
                        <i class="fas fa-route me-2" style="color:var(--verde_azul);"></i>
                        {{ $ruta->titulo ?? 'Ruta sin título' }}
                    </h4>
                    <span class="{{ $esHoy ? 'badge-hoy' : 'badge-dia' }}">
                        <i class="fas fa-calendar-day me-1"></i>{{ $ruta->dia_reparto }}
                    </span>
                </div>
                @if($esHoy && $tieneAsignacion)
                    <form method="POST" action="{{ route('repartidor.iniciar') }}">
                        @csrf
                        <input type="hidden" name="asignacion_id" value="{{ $asignacion['_id'] ?? '' }}">
                        <button type="submit" class="btn-iniciar">
                            <i class="fas fa-play-circle me-2"></i>Iniciar Ruta
                        </button>
                    </form>
                @endif
            </div>

            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong style="color:var(--azul_2);">Clientes en ruta:</strong></p>
                    <div class="clientes-preview">
                        @if(!empty($ruta->clientes))
                            @foreach($ruta->clientes as $cliente)
                                <span class="cliente-mini">
                                    <i class="fas fa-home me-1"></i>
                                    {{ is_array($cliente) ? $cliente['nombre'] : $cliente }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted">Sin clientes asignados</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    @if($tieneAsignacion)
                        <p class="mb-2"><strong style="color:var(--azul_2);">Garrafones asignados:</strong></p>
                        @foreach($asignacion['garrafones'] ?? [] as $g)
                            <small class="d-block">
                                <i class="fas fa-tint me-1" style="color:var(--azul_1);"></i>
                                {{ is_array($g) ? $g['nombre'] : $g }}: 
                                <strong>{{ is_array($g) ? $g['cantidad'] : '?' }}</strong>
                            </small>
                        @endforeach
                    @else
                        <p class="text-muted"><i class="fas fa-info-circle me-1"></i>Sin asignación activa para hoy</p>
                    @endif
                </div>
            </div>
        </div>

    @empty
        <div class="sin-rutas">
            <i class="fas fa-route"></i>
            <h4 style="color:var(--azul_2);">No tienes rutas asignadas</h4>
            <p class="text-muted">Contacta al administrador para que te asigne rutas de reparto</p>
        </div>
    @endforelse

</div>
</div>

@endsection
