@extends('layouts.app')
@section('content')

<div class="ventas-container">
    <div class="bg-elements">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <div class="container py-4">

        <!-- Header -->
        <div class="ventas-header">
            <div class="header-icon"><i class="fas fa-history"></i></div>
            <div class="header-title">
                <h4>Historial de Ventas</h4>
                <p>Gestiona y consulta todos los tickets generados</p>
            </div>
            <div class="header-deco">
                <span>🧾</span><span>💧</span><span>📊</span>
            </div>
            <a href="{{ route('ventas.reportes') }}" class="btn-header">
                <i class="fas fa-chart-line"></i> Ver Estadísticas
            </a>
        </div>

        <!-- Card principal -->
        <div class="ventas-card">

            <!-- Filtros -->
            <div class="filtros-container">
                <div class="filtro-busqueda">
                    <i class="fas fa-search"></i>
                    <input type="text" class="filtro-input" placeholder="Buscar por ticket, mesa o cliente...">
                </div>
                <div class="filtro-estado">
                    <select class="filtro-select">
                        <option selected>Todos los estados</option>
                        <option>Completada</option>
                        <option>Pendiente</option>
                        <option>Cancelada</option>
                    </select>
                </div>
            </div>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table ventas-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha y Hora</th>
                            <th>Mesa</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas as $venta)
                        <tr>
                            <td data-label="Folio">
                                <span class="folio-badge">#{{ $venta->folio ?? str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td data-label="Fecha">
                                @if($venta->created_at)
                                    <div class="fecha-small">{{ $venta->created_at->format('d/m/Y') }}</div>
                                    <div class="hora-small">{{ $venta->created_at->format('h:i A') }}</div>
                                @else
                                    <span class="text-muted">Sin fecha</span>
                                @endif
                            </td>
                            <td data-label="Mesa">
                                <span class="mesa-badge">Mesa {{ $venta->mesa }}</span>
                            </td>
                            <td data-label="Productos">
                                <div class="productos-lista">
                                    @if(is_array($venta->productos) || is_object($venta->productos))
                                        @foreach($venta->productos as $p)
                                            <span class="producto-item">{{ $p['nombre'] ?? 'Producto' }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Total">
                                <span class="total-cell">${{ number_format($venta->total, 2) }}</span>
                            </td>
                            <td data-label="Estado">
                                @php $status = strtolower($venta->estado ?? 'pendiente'); @endphp
                                <span class="badge-estado {{ $status }}">{{ ucfirst($status) }}</span>
                            </td>
                            <td data-label="Acciones" class="text-center">
                                <div class="acciones-group">
                                    <a href="{{ route('ventas.show', $venta->id) }}" class="btn-accion ver" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn-accion imprimir" title="Imprimir Ticket" onclick="imprimirTicket('{{ $venta->id }}')">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-folder-open fa-3x mb-3" style="color:#a8dadc; opacity:.5;"></i>
                                    <h5 style="color:#132d46;">No hay ventas registradas</h5>
                                    <p style="color:#457b9d;">Las ventas aparecerán aquí cuando se realicen.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($ventas) && method_exists($ventas, 'links'))
                <div class="pagination-container">{{ $ventas->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal imprimir -->
<div id="modalImprimir" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:400px;">
        <div class="modal-head"><i class="fas fa-print"></i> Imprimir Ticket</div>
        <div class="modal-body text-center">
            <p style="color:#132d46;">¿Deseas ver el ticket de esta venta?</p>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn-cancel" onclick="cerrarModalImprimir()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" class="btn-confirmar" onclick="confirmarImpresion()">
                <i class="fas fa-print"></i> Ver ticket
            </button>
        </div>
    </div>
</div>

<script>
function imprimirTicket(id) {
    document.getElementById('modalImprimir').style.display = 'flex';
    window.ticketId = id;
}
function cerrarModalImprimir() {
    document.getElementById('modalImprimir').style.display = 'none';
}
function confirmarImpresion() {
    window.location.href = '/ventas/ticket/' + window.ticketId;
    cerrarModalImprimir();
}
window.onclick = e => { if (e.target.id === 'modalImprimir') cerrarModalImprimir(); }
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

.ventas-container {
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

.ventas-header {
    background: linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color: var(--blanco); padding: 25px 30px;
    display: flex; align-items: center; gap: 20px;
    border-radius: 24px 24px 0 0;
    position: relative; overflow: hidden; z-index: 10; flex-wrap: wrap;
}
.ventas-header::after {
    content:''; position:absolute; top:0; right:0; width:180px; height:100%;
    background:linear-gradient(90deg, transparent, rgba(255,255,255,0.08));
    transform:skewX(-20deg) translateX(80px); animation:shine 4s infinite;
}
@keyframes shine { 0%{transform:skewX(-20deg) translateX(80px)} 20%,100%{transform:skewX(-20deg) translateX(-220px)} }

.header-icon {
    width:58px; height:58px; background:rgba(255,255,255,0.15); border-radius:18px;
    display:flex; align-items:center; justify-content:center; font-size:1.8rem;
}
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

.ventas-card {
    background: rgba(255,255,255,0.97); border-radius: 0 0 24px 24px;
    padding: 28px; box-shadow: 0 20px 40px rgba(19,45,70,0.12);
    position: relative; z-index: 10;
}

/* Filtros */
.filtros-container { display:flex; gap:14px; margin-bottom:24px; flex-wrap:wrap; }
.filtro-busqueda { flex:1 1 280px; position:relative; }
.filtro-busqueda i { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--azul_1); }
.filtro-input {
    width:100%; padding:11px 15px 11px 42px;
    border:2px solid var(--azul_3); border-radius:50px; font-size:.93rem; transition:all .3s;
}
.filtro-input:focus { outline:none; border-color:var(--azul_1); box-shadow:0 0 0 3px rgba(69,123,157,.15); }
.filtro-estado { min-width:190px; }
.filtro-select {
    width:100%; padding:11px 15px; border:2px solid var(--azul_3);
    border-radius:50px; background:white; font-size:.93rem; cursor:pointer;
}
.filtro-select:focus { outline:none; border-color:var(--azul_1); }

/* Tabla */
.ventas-table { width:100%; border-collapse:separate; border-spacing:0 10px; }
.ventas-table thead th {
    background:linear-gradient(145deg, #e8f4f8, #d0eaf0);
    color:var(--azul_2); padding:14px; font-weight:700; border:none;
    font-size:.85rem; text-transform:uppercase; letter-spacing:.4px;
}
.ventas-table thead th:first-child { border-radius:12px 0 0 12px; }
.ventas-table thead th:last-child  { border-radius:0 12px 12px 0; }
.ventas-table tbody tr { background:white; box-shadow:0 3px 10px rgba(19,45,70,.04); transition:all .3s; }
.ventas-table tbody tr:hover { transform:translateY(-3px); box-shadow:0 10px 25px rgba(19,45,70,.12); }
.ventas-table tbody td { padding:14px; vertical-align:middle; border:none; }
.ventas-table tbody td:first-child { border-radius:12px 0 0 12px; }
.ventas-table tbody td:last-child  { border-radius:0 12px 12px 0; }

.folio-badge { background:linear-gradient(135deg, var(--azul_2), var(--azul_1)); color:white; padding:5px 13px; border-radius:30px; font-weight:700; font-size:.88rem; }
.fecha-small { font-weight:700; color:var(--azul_2); font-size:.92rem; }
.hora-small  { font-size:.82rem; color:var(--azul_1); }
.mesa-badge  { background:var(--azul_3); color:var(--azul_2); padding:5px 13px; border-radius:30px; font-weight:600; font-size:.88rem; }
.productos-lista { display:flex; flex-wrap:wrap; gap:5px; }
.producto-item { background:#e8f4f8; padding:3px 10px; border-radius:20px; font-size:.82rem; color:var(--azul_2); }
.total-cell { font-weight:800; color:var(--verde_azul); font-size:1.05rem; }

.badge-estado { display:inline-block; padding:5px 14px; border-radius:30px; font-size:.82rem; font-weight:700; text-transform:uppercase; }
.badge-estado.completada { background:var(--verde_azul); color:var(--azul_2); }
.badge-estado.pendiente  { background:var(--amarillo); color:var(--azul_2); }
.badge-estado.cancelada  { background:#e53e3e; color:white; }

.acciones-group { display:flex; gap:8px; justify-content:center; }
.btn-accion {
    width:34px; height:34px; border-radius:9px; border:none;
    display:inline-flex; align-items:center; justify-content:center;
    color:white; cursor:pointer; transition:all .3s; text-decoration:none;
}
.btn-accion.ver     { background:var(--azul_1); }
.btn-accion.imprimir{ background:var(--azul_2); }
.btn-accion:hover   { transform:translateY(-3px); box-shadow:0 5px 14px rgba(19,45,70,.25); }

.empty-state { text-align:center; padding:40px; }
.pagination-container { margin-top:28px; display:flex; justify-content:center; }

/* Modal */
.modal-overlay {
    position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(19,45,70,.55); backdrop-filter:blur(5px);
    z-index:1000; align-items:center; justify-content:center;
}
.modal-box { background:white; border-radius:22px; overflow:hidden; max-width:520px; width:100%; box-shadow:0 30px 60px rgba(19,45,70,.3); animation:slideUp .3s ease; }
@keyframes slideUp { from{opacity:0;transform:translateY(40px)} to{opacity:1;transform:translateY(0)} }
.modal-head { background:linear-gradient(135deg, var(--azul_2), var(--azul_1)); color:var(--blanco); padding:20px 25px; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:10px; }
.modal-body { padding:25px; }
.modal-foot { padding:15px 25px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid #e8f4f8; }
.btn-cancel   { background:#eef2f5; border:none; padding:10px 20px; border-radius:10px; color:#555; cursor:pointer; font-weight:600; }
.btn-confirmar{ background:linear-gradient(145deg, var(--azul_2), var(--azul_1)); border:none; padding:10px 22px; border-radius:10px; color:white; cursor:pointer; font-weight:700; transition:all .3s; }
.btn-confirmar:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(19,45,70,.3); }

@media (max-width:768px) {
    .ventas-header { flex-direction:column; text-align:center; padding:20px; border-radius:16px 16px 0 0; }
    .header-deco,.btn-header { margin:0 auto; }
    .filtros-container { flex-direction:column; }
    .ventas-table thead { display:none; }
    .ventas-table tbody tr { display:block; margin-bottom:15px; }
    .ventas-table tbody td { display:block; text-align:right; padding:10px 15px; position:relative; border-bottom:1px solid #e8f4f8; border-radius:0 !important; }
    .ventas-table tbody td:before { content:attr(data-label); position:absolute; left:15px; font-weight:700; color:var(--azul_2); }
    .productos-lista { justify-content:flex-end; }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
@endsection
