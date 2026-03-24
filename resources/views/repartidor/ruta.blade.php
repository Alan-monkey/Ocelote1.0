@extends('layouts.app')
@section('content')

<style>
:root { --azul_1:#457b9d; --azul_2:#132d46; --azul_3:#a8dadc; --blanco:#f1faee; --verde_azul:#07cdaf; }
.ruta-container { min-height:100vh; background:linear-gradient(145deg,#e8f4f8,#d0eaf0); padding:30px 0; font-family:'Poppins','Segoe UI',sans-serif; }
.header-ruta { background:linear-gradient(135deg,var(--azul_2),var(--azul_1)); color:white; border-radius:24px; padding:25px 30px; margin-bottom:30px; box-shadow:0 10px 30px rgba(19,45,70,.2); }
.camino-container { background:white; border-radius:24px; padding:40px 30px; box-shadow:0 8px 20px rgba(19,45,70,.08); margin-bottom:30px; overflow-x:auto; }

/* Camino */
.camino-wrap { min-width:600px; position:relative; }
.camino-road { position:relative; height:180px; background:linear-gradient(to bottom,
    #c8e6c9 0%, #c8e6c9 38%,
    #78909c 38%, #78909c 44%,
    #fff 44%, #fff 46%,
    #78909c 46%, #78909c 52%,
    #c8e6c9 52%, #c8e6c9 100%
); border-radius:12px; display:flex; align-items:center; padding:0 20px; }

.camion-wrap { position:absolute; left:10px; top:50%; transform:translateY(-50%); z-index:10; font-size:2.8rem; color:var(--azul_1); animation:bounce 1.5s ease-in-out infinite; }
@keyframes bounce { 0%,100%{transform:translateY(-50%);} 50%{transform:translateY(calc(-50% - 6px));} }

.casas-row { display:flex; justify-content:space-around; align-items:flex-end; width:100%; padding-left:110px; padding-bottom:10px; position:relative; z-index:5; }

.casa { text-align:center; position:relative; cursor:pointer; transition:all .3s; }
.casa-icon { font-size:2.4rem; color:var(--azul_2); transition:all .3s; display:block; }
.casa:hover .casa-icon { transform:scale(1.15); color:var(--verde_azul); }
.casa.entregado .casa-icon { color:#10b981; }
.casa-orden { position:absolute; top:-12px; right:-8px; background:var(--azul_1); color:white; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700; }
.casa.entregado .casa-orden { background:#10b981; }
.check-badge { position:absolute; top:-8px; left:-8px; background:#10b981; color:white; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; font-size:.75rem; }
.casa-nombre { font-size:.72rem; font-weight:700; color:var(--azul_2); margin-top:6px; max-width:90px; word-wrap:break-word; line-height:1.2; }
.btn-entregar { background:var(--verde_azul); color:white; border:none; border-radius:8px; padding:5px 12px; font-size:.75rem; font-weight:600; margin-top:6px; cursor:pointer; transition:all .2s; display:block; width:100%; }
.btn-entregar:hover { background:var(--azul_1); }
.btn-entregar:disabled { background:#9ca3af; cursor:not-allowed; }

/* Progreso */
.progreso-bar { background:#e5e7eb; border-radius:20px; height:14px; overflow:hidden; margin-top:24px; }
.progreso-fill { background:linear-gradient(90deg,var(--verde_azul),var(--azul_1)); height:100%; transition:width .5s ease; border-radius:20px; }
</style>

<div class="ruta-container">
<div class="container">

    <div class="header-ruta">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-1 fw-bold"><i class="fas fa-route me-2"></i>{{ $ruta->titulo ?? 'Ruta' }}</h3>
                <p class="mb-0" style="opacity:.85;">{{ $ruta->dia_reparto ?? '' }} — {{ count((array)($ruta->clientes ?? [])) }} clientes</p>
            </div>
            <a href="{{ route('repartidor.dashboard') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="camino-container">
        <h5 class="fw-bold mb-4" style="color:var(--azul_2);">
            <i class="fas fa-map-marked-alt me-2"></i>Recorrido de Entrega
        </h5>

        <div class="camino-wrap">
            <div class="camino-road">
                <div class="camion-wrap">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="casas-row">
                    @foreach($ruta->clientes ?? [] as $index => $cliente)
                        @php
                            $entregasArr = is_object($asignacion) ? ($asignacion->entregas ?? []) : ($asignacion['entregas'] ?? []);
                            $entregado = in_array($index, $entregasArr);
                            $nombreCliente = is_array($cliente) ? $cliente['nombre'] : $cliente;
                        @endphp
                        <div class="casa {{ $entregado ? 'entregado' : '' }}" id="casa-{{ $index }}">
                            <div class="casa-orden">{{ $index + 1 }}</div>
                            @if($entregado)
                                <div class="check-badge"><i class="fas fa-check"></i></div>
                            @endif
                            <i class="fas fa-home casa-icon"></i>
                            <div class="casa-nombre">{{ $nombreCliente }}</div>
                            <button class="btn-entregar" onclick="marcarEntrega({{ $index }})" {{ $entregado ? 'disabled' : '' }}>
                                {{ $entregado ? '✓ Entregado' : 'Entregar' }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @php
            $totalClientes = count((array)($ruta->clientes ?? []));
            $entregasArr   = is_object($asignacion) ? ($asignacion->entregas ?? []) : ($asignacion['entregas'] ?? []);
            $entregados    = count($entregasArr);
            $progreso      = $totalClientes > 0 ? ($entregados / $totalClientes) * 100 : 0;
        @endphp

        <div class="progreso-bar">
            <div class="progreso-fill" id="progresoFill" style="width:{{ $progreso }}%"></div>
        </div>
        <p class="text-center mt-2 mb-0 fw-bold" style="color:var(--azul_2);">
            {{ $entregados }} / {{ $totalClientes }} entregas — {{ round($progreso) }}%
        </p>
    </div>

</div>
</div>

<script>
function marcarEntrega(clienteIndex) {
    if (!confirm('¿Confirmar entrega?')) return;

    fetch('{{ route("repartidor.marcar-entrega") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            asignacion_id: '{{ is_object($asignacion) ? $asignacion->_id : $asignacion["_id"] }}',
            cliente_index: clienteIndex
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Error al marcar entrega');
    });
}
</script>

@endsection
