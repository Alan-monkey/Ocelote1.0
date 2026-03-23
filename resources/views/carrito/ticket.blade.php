@extends('layouts.app')
@section('content')

<div class="ticket-container">
    <div class="bg-elements">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="ticket-card">

                    <!-- Header -->
                    <div class="ticket-header">
                        <div class="ticket-logo">
                            <i class="fas fa-droplet"></i>
                        </div>
                        <h2>¡Pedido Confirmado!</h2>
                        <p>Tu orden ha sido registrada exitosamente</p>
                    </div>

                    <!-- Body -->
                    <div class="ticket-body">

                        <!-- Info general -->
                        <div class="ticket-info">
                            <div class="info-row">
                                <span><i class="fas fa-hashtag"></i> Folio</span>
                                <strong>#{{ $venta->folio }}</strong>
                            </div>
                            <div class="info-row">
                                <span><i class="fas fa-chair"></i> Mesa</span>
                                <span class="mesa-badge">{{ $venta->mesa }}</span>
                            </div>
                            <div class="info-row">
                                <span><i class="fas fa-calendar"></i> Fecha</span>
                                <strong>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div class="ticket-productos">
                            <h5><i class="fas fa-list"></i> Productos</h5>
                            @foreach($venta->productos as $producto)
                            <div class="producto-row">
                                <div class="producto-detalle">
                                    <span class="producto-nombre">{{ $producto['nombre'] }}</span>
                                    <span class="producto-cantidad">x{{ $producto['cantidad'] }}</span>
                                </div>
                                <span class="producto-precio">${{ number_format($producto['subtotal'], 2) }}</span>
                            </div>
                            @endforeach
                        </div>

                        <!-- Totales -->
                        <div class="ticket-totales">
                            @if(!empty($venta->descuento_puntos) && $venta->descuento_puntos > 0)
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>${{ number_format($venta->subtotal ?? $venta->total, 2) }}</span>
                            </div>
                            <div class="total-row descuento">
                                <span><i class="fas fa-tag"></i> Descuento puntos</span>
                                <span>-${{ number_format($venta->descuento_puntos, 2) }}</span>
                            </div>
                            @endif
                            <div class="total-row total-grande">
                                <span>Total</span>
                                <strong>${{ number_format($venta->total, 2) }}</strong>
                            </div>
                            <div class="total-row">
                                <span>Efectivo recibido</span>
                                <span>${{ number_format($venta->efectivo_recibido, 2) }}</span>
                            </div>
                            <div class="total-row cambio-row">
                                <span>Cambio</span>
                                <strong>${{ number_format($venta->cambio, 2) }}</strong>
                            </div>
                        </div>

                        <!-- Mensaje -->
                        <div class="ticket-mensaje">
                            <i class="fas fa-bell"></i>
                            <p>El mesero llevará tu orden a la <strong>Mesa {{ $venta->mesa }}</strong></p>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="ticket-footer">
                        <a href="{{ URL('/carrito') }}" class="btn-ticket secondary">
                            <i class="fas fa-home"></i> Volver al inicio
                        </a>
                        <button onclick="window.print()" class="btn-ticket primary">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

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

.ticket-container {
    min-height: 100vh;
    background: linear-gradient(145deg, #e8f4f8 0%, #d0eaf0 100%);
    padding: 20px 0;
    font-family: 'Poppins', sans-serif;
    position: relative;
    overflow-x: hidden;
}

/* Burbujas */
.bg-elements { position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:0; }
.bubble { position:absolute; border-radius:50%; opacity:.07; animation:floatBubble 18s infinite ease-in-out; background:var(--azul_2); }
.b1 { width:300px; height:300px; top:-80px; left:-80px; }
.b2 { width:200px; height:200px; bottom:10%; right:-60px; animation-delay:6s; }
.b3 { width:150px; height:150px; top:40%; left:5%; animation-delay:12s; }
@keyframes floatBubble {
    0%,100% { transform:translateY(0) scale(1); }
    50% { transform:translateY(-20px) scale(1.05); }
}

/* Card */
.ticket-card {
    background: white;
    border-radius: 28px;
    box-shadow: 0 20px 50px rgba(19,45,70,0.15);
    overflow: hidden;
    animation: slideIn .5s ease;
    position: relative; z-index: 10;
}
@keyframes slideIn {
    from { opacity:0; transform:translateY(30px); }
    to   { opacity:1; transform:translateY(0); }
}

/* Header */
.ticket-header {
    background: linear-gradient(135deg, var(--azul_2) 0%, var(--azul_1) 100%);
    color: var(--blanco);
    padding: 35px 30px;
    text-align: center;
    position: relative; overflow: hidden;
}
.ticket-header::after {
    content:''; position:absolute; top:0; right:0;
    width:180px; height:100%;
    background:linear-gradient(90deg, transparent, rgba(255,255,255,0.07));
    transform:skewX(-20deg) translateX(80px);
    animation:shine 4s infinite;
}
@keyframes shine {
    0%   { transform:skewX(-20deg) translateX(80px); }
    20%  { transform:skewX(-20deg) translateX(-220px); }
    100% { transform:skewX(-20deg) translateX(-220px); }
}
.ticket-logo {
    width:80px; height:80px;
    background:rgba(255,255,255,0.18); border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 18px; font-size:2.4rem;
    animation:pulse 2.5s infinite;
}
@keyframes pulse {
    0%,100% { transform:scale(1); box-shadow:0 0 0 0 rgba(255,255,255,.3); }
    50% { transform:scale(1.08); box-shadow:0 0 0 12px rgba(255,255,255,0); }
}
.ticket-header h2 { font-weight:800; margin-bottom:8px; font-size:1.6rem; }
.ticket-header p  { opacity:.88; margin:0; font-size:.92rem; }

/* Body */
.ticket-body { padding:28px; }

/* Info */
.ticket-info {
    background: linear-gradient(145deg, #e8f4f8, #d8eef5);
    border-radius: 16px; padding:18px; margin-bottom:22px;
    border: 1px solid var(--azul_3);
}
.info-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:9px 0; border-bottom:1px dashed var(--azul_3);
    font-size:.92rem; color:#3a5a70;
}
.info-row:last-child { border-bottom:none; }
.info-row i { margin-right:6px; color:var(--azul_1); }
.mesa-badge {
    background:linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color:white; padding:5px 18px; border-radius:25px;
    font-size:1.1rem; font-weight:800;
    box-shadow:0 4px 12px rgba(19,45,70,.25);
}

/* Productos */
.ticket-productos { margin-bottom:22px; }
.ticket-productos h5 {
    color:var(--azul_2); font-weight:700; margin-bottom:14px;
    padding-bottom:10px; border-bottom:2px solid var(--azul_3);
    display:flex; align-items:center; gap:8px;
}
.ticket-productos h5 i { color:var(--azul_1); }
.producto-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:10px 0; border-bottom:1px solid #e8f4f8;
}
.producto-detalle { display:flex; align-items:center; gap:10px; }
.producto-nombre { font-weight:600; color:var(--azul_2); font-size:.92rem; }
.producto-cantidad {
    color:var(--azul_1); font-size:.82rem;
    background:#e8f4f8; padding:2px 10px; border-radius:20px; font-weight:600;
}
.producto-precio { font-weight:700; color:var(--verde_azul); font-size:.95rem; }

/* Totales */
.ticket-totales {
    background:#f0f8fb; border-radius:16px; padding:18px;
    margin-bottom:22px; border:1px solid var(--azul_3);
}
.total-row {
    display:flex; justify-content:space-between;
    padding:7px 0; font-size:.95rem; color:#3a5a70;
}
.total-row.descuento { color:#7a6000; font-weight:600; }
.total-row.total-grande {
    font-size:1.15rem; font-weight:800; color:var(--azul_2);
    border-top:2px solid var(--azul_3); margin-top:8px; padding-top:12px;
}
.total-row.cambio-row {
    color:var(--verde_azul); font-weight:700; font-size:1rem;
    border-top:1px dashed var(--azul_3); margin-top:4px; padding-top:10px;
}

/* Mensaje */
.ticket-mensaje {
    background:linear-gradient(135deg, #d0f4ee, #a8f0e0);
    border-radius:16px; padding:20px; text-align:center;
    color:#065f46; border:1px solid var(--verde_azul);
}
.ticket-mensaje i { font-size:2rem; margin-bottom:8px; display:block; }
.ticket-mensaje p { margin:0; font-weight:500; font-size:.95rem; }

/* Footer */
.ticket-footer {
    padding:20px 28px;
    background:#f0f8fb;
    display:flex; gap:12px; justify-content:center; flex-wrap:wrap;
    border-top:1px solid var(--azul_3);
}
.btn-ticket {
    padding:11px 24px; border-radius:12px; font-weight:700;
    text-decoration:none; transition:all .3s;
    display:inline-flex; align-items:center; gap:8px;
    font-size:.92rem; border:none; cursor:pointer;
}
.btn-ticket.secondary {
    background:linear-gradient(145deg, #5a7a8a, #3d5a6e);
    color:white;
}
.btn-ticket.primary {
    background:linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color:white;
}
.btn-ticket:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(19,45,70,.25); color:white; }

@media print {
    .ticket-footer, .bg-elements { display:none; }
    .ticket-container { background:white; }
    .ticket-card { box-shadow:none; border:1px solid #ddd; border-radius:0; }
}

@media (max-width:480px) {
    .ticket-footer { flex-direction:column; }
    .btn-ticket { justify-content:center; }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

@endsection
