@extends('layouts.app')
@section('content')

<div class="reportes-container">
    <div class="bg-elements">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <div class="container py-4">

        <!-- Header -->
        <div class="reportes-header">
            <div class="header-icon"><i class="fas fa-chart-line"></i></div>
            <div class="header-title">
                <h4>Reportes de Hoy</h4>
                <p>Resumen de ventas y productos del día</p>
            </div>
            <div class="header-deco">
                <span>📈</span><span>💧</span><span>📊</span>
            </div>
            <a href="{{ route('ventas.index') }}" class="btn-header">
                <i class="fas fa-history"></i> Ver Historial
            </a>
        </div>

        <!-- Card principal -->
        <div class="reportes-card">

            <!-- Resumen tarjetas -->
            <div class="resumen-ventas">
                <div class="resumen-card total">
                    <div class="card-icono"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="card-contenido">
                        <span class="card-label">Total Vendido Hoy</span>
                        <span class="card-valor">${{ number_format($totalHoy, 2) }}</span>
                    </div>
                </div>
                <div class="resumen-card tickets">
                    <div class="card-icono"><i class="fas fa-receipt"></i></div>
                    <div class="card-contenido">
                        <span class="card-label">Tickets Generados</span>
                        <span class="card-valor">{{ $ventasHoy->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Gráfico + Detalle -->
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="grafico-card">
                        <h5 class="seccion-titulo">
                            <i class="fas fa-crown" style="color:var(--amarillo);"></i> Top 5 Productos de Hoy
                        </h5>
                        <div class="grafico-contenedor">
                            <canvas id="graficaProductos"></canvas>
                        </div>
                        @if(count($masVendidos) == 0)
                            <p class="text-center mt-4" style="color:var(--azul_1);">No hay ventas registradas hoy.</p>
                        @endif
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="detalle-card">
                        <h5 class="seccion-titulo">
                            <i class="fas fa-clock" style="color:var(--azul_1);"></i> Detalle de Ventas — {{ date('d/m/Y') }}
                        </h5>
                        <div class="table-responsive">
                            <table class="table detalle-table">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Mesa</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ventasHoy as $venta)
                                    <tr>
                                        <td data-label="Hora">
                                            <span class="hora-badge">{{ $venta->created_at->format('h:i A') }}</span>
                                        </td>
                                        <td data-label="Mesa">
                                            <span class="mesa-badge">Mesa {{ $venta->mesa }}</span>
                                        </td>
                                        <td data-label="Total" class="text-end">
                                            <span class="total-badge">${{ number_format($venta->total, 2) }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <i class="fas fa-receipt fa-2x mb-2" style="color:#a8dadc; opacity:.5;"></i>
                                            <p style="color:var(--azul_1);">Sin ventas aún</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(count($masVendidos) > 0)
    const ctx = document.getElementById('graficaProductos').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($masVendidos)) !!},
            datasets: [{
                data: {!! json_encode(array_values($masVendidos)) !!},
                backgroundColor: ['#132d46','#457b9d','#07cdaf','#a8dadc','#e0d205'],
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding:18, font:{ size:12, family:'Poppins' }, color:'#132d46' }
                }
            },
            cutout: '65%'
        }
    });
    @endif
});
</script>

<style>
:root {
    --azul_1: #457b9d;
    --azul_2: #132d46;
    --azul_3: #a8dadc;
    --blanco: #f1faee;
    --negrito: #151613;
    --verde_azul: #07cdaf;
    --amarillo_claro: #fffaca;
    --amarillo: #e0d205;
}

.reportes-container {
    position: relative; min-height: 100vh;
    background: linear-gradient(145deg, #e8f4f8 0%, #d0eaf0 100%);
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    padding: 20px 0; overflow-x: hidden;
}

.bg-elements { position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:0; }
.bubble { position:absolute; border-radius:50%; opacity:.07; animation:floatBubble 18s infinite ease-in-out; background:var(--azul_2); }
.b1 { width:300px; height:300px; top:-80px; left:-80px; }
.b2 { width:200px; height:200px; bottom:10%; right:-60px; animation-delay:6s; }
.b3 { width:150px; height:150px; top:40%; left:5%; animation-delay:12s; }
@keyframes floatBubble { 0%,100%{transform:translateY(0) scale(1)} 50%{transform:translateY(-20px) scale(1.05)} }

.reportes-header {
    background: linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color: var(--blanco); padding: 25px 30px;
    display: flex; align-items: center; gap: 20px;
    border-radius: 24px 24px 0 0;
    position: relative; overflow: hidden; z-index: 10; flex-wrap: wrap;
}
.reportes-header::after {
    content:''; position:absolute; top:0; right:0; width:180px; height:100%;
    background:linear-gradient(90deg, transparent, rgba(255,255,255,0.08));
    transform:skewX(-20deg) translateX(80px); animation:shine 4s infinite;
}
@keyframes shine { 0%{transform:skewX(-20deg) translateX(80px)} 20%,100%{transform:skewX(-20deg) translateX(-220px)} }

.header-icon { width:58px; height:58px; background:rgba(255,255,255,0.15); border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; }
.header-title h4 { margin:0; font-weight:700; font-size:1.4rem; }
.header-title p  { margin:4px 0 0; opacity:.85; font-size:.88rem; }
.header-deco { font-size:1.3rem; }
.header-deco span { margin:0 4px; display:inline-block; animation:bounce 2s infinite; }
@keyframes bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }

.btn-header {
    background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.4);
    color:white; padding:9px 20px; border-radius:50px;
    text-decoration:none; font-weight:600; font-size:.88rem;
    display:inline-flex; align-items:center; gap:7px; transition:all .3s;
}
.btn-header:hover { background:var(--verde_azul); border-color:var(--verde_azul); color:var(--azul_2); transform:translateY(-2px); }

.reportes-card {
    background: rgba(255,255,255,0.97); border-radius: 0 0 24px 24px;
    padding: 28px; box-shadow: 0 20px 40px rgba(19,45,70,0.12);
    position: relative; z-index: 10;
}

/* Resumen */
.resumen-ventas { display:grid; grid-template-columns:repeat(auto-fit, minmax(240px,1fr)); gap:20px; margin-bottom:28px; }
.resumen-card {
    display:flex; align-items:center; gap:18px;
    background:white; padding:20px; border-radius:20px;
    box-shadow:0 5px 15px rgba(19,45,70,.06);
    border:1px solid rgba(69,123,157,.15); transition:all .3s;
}
.resumen-card:hover { transform:translateY(-5px); box-shadow:0 15px 30px rgba(19,45,70,.14); }
.resumen-card.total   .card-icono { background:linear-gradient(145deg, var(--verde_azul), #05b89a); }
.resumen-card.tickets .card-icono { background:linear-gradient(145deg, var(--azul_1), var(--azul_2)); }
.card-icono { width:58px; height:58px; border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:white; flex-shrink:0; }
.card-label { display:block; color:var(--azul_1); font-size:.85rem; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
.card-valor { display:block; font-size:1.9rem; font-weight:800; color:var(--azul_2); }

/* Gráfico y detalle */
.grafico-card, .detalle-card {
    background:white; border-radius:20px; padding:22px; height:100%;
    box-shadow:0 5px 15px rgba(19,45,70,.05);
    border:1px solid rgba(69,123,157,.12);
}
.seccion-titulo { color:var(--azul_2); font-weight:700; margin-bottom:18px; display:flex; align-items:center; gap:8px; font-size:1rem; }
.grafico-contenedor { position:relative; height:280px; width:100%; }

/* Tabla detalle */
.detalle-table { width:100%; border-collapse:separate; border-spacing:0 8px; }
.detalle-table thead th {
    background:linear-gradient(145deg, #e8f4f8, #d0eaf0);
    color:var(--azul_2); padding:12px; font-weight:700; border:none; font-size:.85rem;
}
.detalle-table thead th:first-child { border-radius:10px 0 0 10px; }
.detalle-table thead th:last-child  { border-radius:0 10px 10px 0; }
.detalle-table tbody tr { background:#f8fcfd; transition:all .3s; }
.detalle-table tbody tr:hover { background:#e8f4f8; transform:translateY(-2px); }
.detalle-table tbody td { padding:12px; vertical-align:middle; border:none; }
.detalle-table tbody td:first-child { border-radius:10px 0 0 10px; }
.detalle-table tbody td:last-child  { border-radius:0 10px 10px 0; }

.hora-badge  { background:linear-gradient(135deg, var(--azul_2), var(--azul_1)); color:white; padding:5px 13px; border-radius:30px; font-size:.88rem; font-weight:700; }
.mesa-badge  { background:var(--azul_3); color:var(--azul_2); padding:5px 13px; border-radius:30px; font-size:.88rem; font-weight:600; }
.total-badge { font-weight:800; color:var(--verde_azul); font-size:1.05rem; }

@media (max-width:768px) {
    .reportes-header { flex-direction:column; text-align:center; padding:20px; border-radius:16px 16px 0 0; }
    .header-deco,.btn-header { margin:0 auto; }
    .resumen-ventas { grid-template-columns:1fr; }
    .detalle-table thead { display:none; }
    .detalle-table tbody tr { display:block; margin-bottom:12px; }
    .detalle-table tbody td { display:block; text-align:right; padding:10px 15px; position:relative; border-bottom:1px solid #e8f4f8; border-radius:0 !important; }
    .detalle-table tbody td:before { content:attr(data-label); position:absolute; left:15px; font-weight:700; color:var(--azul_2); }
}
</style>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection
