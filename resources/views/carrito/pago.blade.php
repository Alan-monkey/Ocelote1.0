@extends('layouts.app')
@section('content')

<div class="pago-container">
    <div class="bg-elements">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <div class="container py-4">

        @if(session('error'))
            <div class="alert-custom error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <!-- Header -->
        <div class="pago-header">
            <div class="header-icon"><i class="fas fa-cash-register"></i></div>
            <div class="header-title">
                <h4>Procesar Pago</h4>
                <p>Confirma los productos y registra el cobro</p>
            </div>
            <a href="{{ route('carrito.ver') }}" class="btn-volver ms-auto">
                <i class="fas fa-arrow-left"></i> Volver al carrito
            </a>
        </div>

        <div class="pago-grid">

            <!-- Resumen del pedido -->
            <div class="pago-card">
                <div class="card-section-title">
                    <i class="fas fa-receipt"></i> Resumen del pedido
                </div>

                <div class="items-lista">
                    @foreach($carrito as $item)
                    <div class="item-row">
                        <div class="item-info">
                            @if(!empty($item['imagen']))
                                <img src="{{ asset('storage/' . $item['imagen']) }}" alt="{{ $item['nombre'] }}" class="item-thumb">
                            @else
                                <div class="item-thumb-placeholder"><i class="fas fa-droplet"></i></div>
                            @endif
                            <div>
                                <span class="item-nombre">{{ $item['nombre'] }}</span>
                                <span class="item-cantidad">x{{ $item['cantidad'] }}</span>
                            </div>
                        </div>
                        <span class="item-subtotal">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="total-box">
                    <span>Total</span>
                    <span class="total-monto" id="totalMonto">${{ number_format($total, 2) }}</span>
                </div>
            </div>

            <!-- Formulario de cobro -->
            <div class="pago-card">
                <div class="card-section-title">
                    <i class="fas fa-coins"></i> Datos del cobro
                </div>

                <form action="{{ route('carrito.procesar-pago') }}" method="POST" id="formPago">
                    @csrf

                    <!-- Mesa -->
                    <div class="form-group mb-4">
                        <label class="form-label"><i class="fas fa-chair"></i> Número de mesa *</label>
                        <div class="mesas-grid">
                            @for($i = 1; $i <= 10; $i++)
                            <label class="mesa-option">
                                <input type="radio" name="mesa" value="{{ $i }}" {{ $i == 1 ? 'checked' : '' }}>
                                <span>{{ $i }}</span>
                            </label>
                            @endfor
                        </div>
                    </div>

                    @if($user->user_tipo == 1 && ($user->puntos ?? 0) > 0)
                    <!-- Puntos (solo clientes con puntos) -->
                    <div class="form-group mb-4 puntos-section">
                        <label class="form-label">
                            <i class="fas fa-star"></i> Usar puntos
                            <span class="puntos-disponibles">Disponibles: {{ $user->puntos ?? 0 }} pts</span>
                        </label>
                        <div class="puntos-input-wrap">
                            <input type="number" name="puntos_a_usar" id="puntosInput"
                                   class="form-control" min="0" max="{{ min($user->puntos ?? 0, $total) }}"
                                   value="0" placeholder="0">
                            <span class="puntos-equiv" id="puntosEquiv">= $0.00 de descuento</span>
                        </div>
                        <input type="range" id="puntosRange" min="0" max="{{ min($user->puntos ?? 0, $total) }}"
                               value="0" class="puntos-range">
                    </div>
                    @else
                        <input type="hidden" name="puntos_a_usar" value="0">
                    @endif

                    <!-- Total final -->
                    <div class="total-final-box">
                        <div class="total-final-row">
                            <span>Subtotal</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="total-final-row descuento" id="rowDescuento" style="display:none;">
                            <span><i class="fas fa-tag"></i> Descuento puntos</span>
                            <span id="descuentoMonto">-$0.00</span>
                        </div>
                        <div class="total-final-row total-grande">
                            <span>Total a cobrar</span>
                            <span id="totalFinal">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Efectivo recibido -->
                    <div class="form-group mb-3">
                        <label class="form-label"><i class="fas fa-money-bill-wave"></i> Efectivo recibido *</label>
                        <input type="number" name="efectivo_recibido" id="efectivoInput"
                               class="form-control form-control-lg" min="0" step="0.01"
                               placeholder="0.00" required>
                    </div>

                    <!-- Cambio -->
                    <div class="cambio-box" id="cambioBox" style="display:none;">
                        <i class="fas fa-coins"></i>
                        <div>
                            <span class="cambio-label">Cambio a entregar</span>
                            <span class="cambio-monto" id="cambioMonto">$0.00</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-pagar" id="btnPagar">
                        <i class="fas fa-check-circle"></i> Confirmar pago
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
const totalBase = {{ $total }};
let descuento = 0;

// Puntos
const puntosInput = document.getElementById('puntosInput');
const puntosRange = document.getElementById('puntosRange');

if (puntosInput) {
    function actualizarPuntos(val) {
        val = Math.min(val, parseFloat(puntosInput.max));
        descuento = parseFloat(val) || 0;
        puntosInput.value = descuento;
        if (puntosRange) puntosRange.value = descuento;

        const totalFinal = Math.max(0, totalBase - descuento);
        document.getElementById('totalFinal').textContent = '$' + totalFinal.toFixed(2);
        document.getElementById('puntosEquiv').textContent = '= $' + descuento.toFixed(2) + ' de descuento';

        const rowDesc = document.getElementById('rowDescuento');
        if (descuento > 0) {
            rowDesc.style.display = 'flex';
            document.getElementById('descuentoMonto').textContent = '-$' + descuento.toFixed(2);
        } else {
            rowDesc.style.display = 'none';
        }
        calcularCambio();
    }

    puntosInput.addEventListener('input', () => actualizarPuntos(puntosInput.value));
    if (puntosRange) puntosRange.addEventListener('input', () => actualizarPuntos(puntosRange.value));
}

// Cambio
function calcularCambio() {
    const efectivo = parseFloat(document.getElementById('efectivoInput').value) || 0;
    const totalFinal = Math.max(0, totalBase - descuento);
    const cambio = efectivo - totalFinal;
    const box = document.getElementById('cambioBox');

    if (efectivo > 0) {
        box.style.display = 'flex';
        document.getElementById('cambioMonto').textContent = '$' + Math.max(0, cambio).toFixed(2);
        box.classList.toggle('insuficiente', cambio < 0);
        document.getElementById('cambioMonto').textContent = cambio < 0
            ? '-$' + Math.abs(cambio).toFixed(2) + ' (insuficiente)'
            : '$' + cambio.toFixed(2);
    } else {
        box.style.display = 'none';
    }
}

document.getElementById('efectivoInput').addEventListener('input', calcularCambio);

// Auto-cerrar alertas
setTimeout(() => {
    document.querySelectorAll('.alert-custom').forEach(a => a.style.opacity = '0');
}, 4000);
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

.pago-container {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(145deg, #e8f4f8 0%, #d0eaf0 100%);
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    padding: 20px 0;
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

/* Alerta */
.alert-custom {
    padding:14px 20px; border-radius:12px; margin-bottom:20px;
    font-weight:600; display:flex; align-items:center; gap:10px;
    transition:opacity .5s; position:relative; z-index:10;
}
.alert-custom.error { background:#fde8e8; color:#7b1d1d; border-left:5px solid #e53e3e; }

/* Header */
.pago-header {
    background: linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color: var(--blanco);
    padding: 22px 30px;
    display: flex; align-items: center; gap: 18px;
    border-radius: 24px;
    position: relative; overflow: hidden; z-index: 10;
    margin-bottom: 28px;
}
.pago-header::after {
    content:''; position:absolute; top:0; right:0;
    width:180px; height:100%;
    background:linear-gradient(90deg, transparent, rgba(255,255,255,0.08));
    transform:skewX(-20deg) translateX(80px);
    animation:shine 4s infinite;
}
@keyframes shine {
    0%   { transform:skewX(-20deg) translateX(80px); }
    20%  { transform:skewX(-20deg) translateX(-220px); }
    100% { transform:skewX(-20deg) translateX(-220px); }
}
.header-icon {
    width:54px; height:54px;
    background:rgba(255,255,255,0.15); border-radius:16px;
    display:flex; align-items:center; justify-content:center; font-size:1.7rem;
}
.header-title h4 { margin:0; font-weight:700; font-size:1.3rem; }
.header-title p  { margin:3px 0 0; opacity:.85; font-size:.85rem; }
.btn-volver {
    background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.4);
    color:white; padding:9px 18px; border-radius:50px;
    text-decoration:none; font-weight:600; font-size:.88rem;
    display:flex; align-items:center; gap:7px;
    transition:all .3s; white-space:nowrap;
}
.btn-volver:hover { background:rgba(255,255,255,0.28); color:white; }

/* Grid */
.pago-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    position: relative; z-index: 10;
}

/* Cards */
.pago-card {
    background: rgba(255,255,255,0.97);
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 15px 35px rgba(19,45,70,0.1);
}
.card-section-title {
    font-size: 1rem; font-weight: 700; color: var(--azul_2);
    margin-bottom: 20px; padding-bottom: 12px;
    border-bottom: 2px solid var(--azul_3);
    display: flex; align-items: center; gap: 8px;
}
.card-section-title i { color: var(--azul_1); }

/* Items */
.items-lista { display:flex; flex-direction:column; gap:12px; margin-bottom:20px; }
.item-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:12px 14px; border-radius:12px;
    background:#f0f8fb; transition:background .2s;
}
.item-row:hover { background:#e0f0f5; }
.item-info { display:flex; align-items:center; gap:12px; }
.item-thumb { width:44px; height:44px; border-radius:10px; object-fit:cover; border:2px solid white; box-shadow:0 2px 8px rgba(19,45,70,.1); }
.item-thumb-placeholder {
    width:44px; height:44px; border-radius:10px;
    background:var(--azul_3); display:flex; align-items:center; justify-content:center;
    color:var(--azul_2); font-size:1.2rem;
}
.item-nombre { font-weight:600; color:var(--azul_2); font-size:.92rem; display:block; }
.item-cantidad { font-size:.82rem; color:var(--azul_1); }
.item-subtotal { font-weight:700; color:var(--azul_1); font-size:.95rem; }

.total-box {
    display:flex; justify-content:space-between; align-items:center;
    padding:14px 16px; border-radius:12px;
    background:linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color:white;
}
.total-box span:first-child { font-weight:600; font-size:.95rem; }
.total-monto { font-size:1.4rem; font-weight:800; }

/* Mesas */
.mesas-grid {
    display:grid; grid-template-columns:repeat(5, 1fr); gap:8px;
}
.mesa-option input { display:none; }
.mesa-option span {
    display:flex; align-items:center; justify-content:center;
    height:44px; border-radius:10px; font-weight:700; font-size:.95rem;
    border:2px solid var(--azul_3); color:var(--azul_1);
    cursor:pointer; transition:all .2s;
}
.mesa-option input:checked + span {
    background:linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color:white; border-color:var(--azul_2);
    box-shadow:0 4px 12px rgba(19,45,70,.25);
}
.mesa-option span:hover { border-color:var(--azul_1); background:#e8f4f8; }

/* Puntos */
.puntos-section { background:#fffef0; border-radius:14px; padding:16px; border:2px solid var(--amarillo); }
.puntos-disponibles { float:right; font-size:.82rem; color:var(--azul_1); font-weight:600; }
.puntos-input-wrap { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
.puntos-equiv { font-size:.82rem; color:#7a6000; font-weight:600; white-space:nowrap; }
.puntos-range {
    width:100%; accent-color:var(--amarillo);
    height:6px; border-radius:3px; cursor:pointer;
}

/* Total final */
.total-final-box {
    background:#f0f8fb; border-radius:14px; padding:16px;
    margin-bottom:20px; border:1px solid var(--azul_3);
}
.total-final-row {
    display:flex; justify-content:space-between;
    padding:6px 0; font-size:.92rem; color:#3a5a70;
}
.total-final-row.descuento { color:#7a6000; font-weight:600; }
.total-final-row.total-grande {
    font-size:1.15rem; font-weight:800; color:var(--azul_2);
    border-top:2px solid var(--azul_3); margin-top:8px; padding-top:12px;
}

/* Form */
.form-control {
    width:100%; padding:11px 15px;
    border:2px solid var(--azul_3); border-radius:12px;
    font-size:.95rem; color:var(--negrito); transition:all .3s;
}
.form-control:focus { outline:none; border-color:var(--azul_1); box-shadow:0 0 0 3px rgba(69,123,157,.15); }
.form-control-lg { font-size:1.1rem; padding:13px 18px; }
.form-label { font-weight:700; color:var(--azul_2); margin-bottom:8px; display:block; font-size:.9rem; }

/* Cambio */
.cambio-box {
    display:flex; align-items:center; gap:14px;
    padding:14px 18px; border-radius:14px;
    background:linear-gradient(135deg, #d0f4ee, #a8f0e0);
    border:2px solid var(--verde_azul);
    margin-bottom:20px;
}
.cambio-box.insuficiente { background:#fde8e8; border-color:#e53e3e; }
.cambio-box i { font-size:1.8rem; color:var(--verde_azul); }
.cambio-box.insuficiente i { color:#e53e3e; }
.cambio-label { display:block; font-size:.82rem; color:#065f46; font-weight:600; }
.cambio-monto { font-size:1.3rem; font-weight:800; color:#065f46; }
.cambio-box.insuficiente .cambio-label,
.cambio-box.insuficiente .cambio-monto { color:#7b1d1d; }

/* Botón pagar */
.btn-pagar {
    width:100%; padding:15px;
    background:linear-gradient(135deg, var(--verde_azul), #05b89a);
    border:none; border-radius:14px;
    color:var(--azul_2); font-size:1.1rem; font-weight:800;
    cursor:pointer; transition:all .3s;
    display:flex; align-items:center; justify-content:center; gap:10px;
    box-shadow:0 6px 20px rgba(7,205,175,.35);
}
.btn-pagar:hover { transform:translateY(-3px); box-shadow:0 10px 28px rgba(7,205,175,.45); }
.btn-pagar:active { transform:translateY(0); }

@media (max-width:768px) {
    .pago-grid { grid-template-columns:1fr; }
    .pago-header { flex-direction:column; text-align:center; border-radius:16px; }
    .btn-volver { margin:0 auto; }
    .mesas-grid { grid-template-columns:repeat(5,1fr); }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

@endsection
