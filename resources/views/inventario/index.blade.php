@extends('layouts.app')
@section('content')

<div class="inventario-container">
    <div class="bg-elements">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <div class="container py-4">

        @if(session('success'))
            <div class="alert-custom success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-custom error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <!-- Header -->
        <div class="inventario-header">
            <div class="header-icon"><i class="fas fa-warehouse"></i></div>
            <div class="header-title">
                <h4>Control de Inventario</h4>
                <p>Gestión de stock de productos</p>
            </div>
            <div class="header-deco ms-auto">
                <span>📦</span><span>💧</span><span>📊</span>
            </div>
        </div>

        <!-- Tarjeta principal -->
        <div class="inventario-card">

            <!-- Resumen -->
            <div class="inventario-resumen">
                <div class="resumen-card">
                    <i class="fas fa-cubes"></i>
                    <span class="resumen-valor">{{ $total_stock }}</span>
                    <span class="resumen-label">Total unidades</span>
                </div>
                <div class="resumen-card">
                    <i class="fas fa-boxes"></i>
                    <span class="resumen-valor">{{ count($productos_con_inventario) }}</span>
                    <span class="resumen-label">Productos</span>
                </div>
                <div class="resumen-card warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="resumen-valor">{{ $productos_bajo_stock }}</span>
                    <span class="resumen-label">Stock bajo</span>
                </div>
            </div>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table inventario-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock actual</th>
                            <th>Stock mínimo</th>
                            <th>Estado</th>
                            <th>Última actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos_con_inventario as $producto)
                        <tr>
                            <td>
                                <div class="producto-info-cell">
                                    @if($producto->imagen)
                                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="producto-thumb">
                                    @else
                                        <div class="producto-thumb-placeholder"><i class="fas fa-tint"></i></div>
                                    @endif
                                    <strong>{{ $producto->nombre }}</strong>
                                </div>
                            </td>
                            <td><span class="precio-cell">${{ number_format($producto->precio, 2) }}</span></td>
                            <td>
                                <span class="stock-badge {{ $producto->inventario->bajo_stock ? 'bajo' : 'normal' }}">
                                    {{ $producto->inventario->stock_actual }} unidades
                                </span>
                            </td>
                            <td>{{ $producto->inventario->stock_minimo }} unidades</td>
                            <td>
                                @if($producto->inventario->stock_actual == 0)
                                    <span class="badge-estado agotado">Agotado</span>
                                @elseif($producto->inventario->bajo_stock)
                                    <span class="badge-estado bajo">¡Stock bajo!</span>
                                @else
                                    <span class="badge-estado normal">Normal</span>
                                @endif
                            </td>
                            <td>
                                @if($producto->inventario->fecha_actualizacion)
                                    {{ \Carbon\Carbon::parse($producto->inventario->fecha_actualizacion)->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">Sin fecha</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn-ajustar"
                                        onclick="abrirModalAjuste('{{ $producto->_id }}', '{{ addslashes($producto->nombre) }}', {{ $producto->inventario->stock_actual }})">
                                    <i class="fas fa-edit"></i> Ajustar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-box-open fa-4x mb-3" style="color:#a8dadc; opacity:.6;"></i>
                                    <h5 style="color:#132d46;">No hay productos con inventario</h5>
                                    <a href="{{ route('productos.crear') }}" class="btn-crear-producto">
                                        <i class="fas fa-plus-circle"></i> Crear producto
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal ajuste de stock -->
<div id="modalAjuste" class="modal-confirm">
    <div class="modal-content" style="max-width:560px; max-height:90vh; overflow-y:auto;">
        <div class="modal-header">
            <div class="modal-icon"><i class="fas fa-boxes"></i></div>
            <h3>Ajustar stock</h3>
        </div>
        <form action="{{ route('inventario.actualizar') }}" method="POST" id="formAjuste">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="producto_id" id="ajuste_producto_id">

                <div class="form-group mb-3">
                    <label class="form-label">Producto</label>
                    <input type="text" class="form-control" id="ajuste_producto_nombre" readonly>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Stock actual</label>
                    <input type="text" class="form-control" id="ajuste_stock_actual" readonly>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Cantidad a agregar <span class="text-danger">*</span></label>
                    <input type="number" name="nuevo_stock" id="ajuste_nuevo_stock" class="form-control" min="1" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Motivo del ajuste <span class="text-danger">*</span></label>
                    <select name="motivo" class="form-control" required>
                        <option value="">Seleccionar motivo</option>
                        <option value="compra">Compra a proveedor</option>
                        <option value="venta">Venta realizada</option>
                        <option value="devolucion">Devolución</option>
                        <option value="inventario_fisico">Inventario físico</option>
                        <option value="ajuste_manual">Ajuste manual</option>
                    </select>
                </div>

                <!-- Insumos -->
                <div class="form-group mb-2">
                    <label class="form-label">
                        Insumos utilizados <span class="text-danger">*</span>
                        <small style="font-weight:400; color:#457b9d;">(selecciona al menos uno)</small>
                    </label>
                    <div id="insumos-lista">
                        @forelse($insumos as $insumo)
                        <label class="insumo-check-item">
                            <input type="checkbox" name="insumos[]" value="{{ $insumo['_id'] }}"
                                   data-nombre="{{ $insumo['nombre'] }}"
                                   data-cantidad="{{ $insumo['cantidad'] }}">
                            <span class="insumo-info">
                                <span class="insumo-nombre">{{ $insumo['nombre'] }}</span>
                                <span class="insumo-stock {{ $insumo['cantidad'] <= $insumo['cantidad_minima'] ? 'bajo' : '' }}">
                                    {{ $insumo['cantidad'] }} {{ $insumo['unidad_medida'] }}
                                </span>
                            </span>
                        </label>
                        @empty
                        <p class="text-center py-2 mb-0" style="color:#457b9d;">No hay insumos registrados.</p>
                        @endforelse
                    </div>
                    <div id="insumos-error">
                        <i class="fas fa-exclamation-circle"></i> Debes seleccionar al menos un insumo.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn-confirmar" onclick="validarYEnviar()">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalAjuste(id, nombre, stockActual) {
    document.getElementById('ajuste_producto_id').value = id;
    document.getElementById('ajuste_producto_nombre').value = nombre;
    document.getElementById('ajuste_stock_actual').value = stockActual + ' unidades';
    document.getElementById('ajuste_nuevo_stock').value = '';
    document.querySelectorAll('#insumos-lista input[type=checkbox]').forEach(c => c.checked = false);
    document.getElementById('insumos-error').style.display = 'none';
    document.getElementById('modalAjuste').style.display = 'flex';
}
function validarYEnviar() {
    const checked = document.querySelectorAll('#insumos-lista input[type=checkbox]:checked');
    if (checked.length === 0) {
        document.getElementById('insumos-error').style.display = 'block';
        return;
    }
    document.getElementById('insumos-error').style.display = 'none';
    document.getElementById('formAjuste').submit();
}
function cerrarModal() {
    document.getElementById('modalAjuste').style.display = 'none';
}
window.onclick = e => { if (e.target.id === 'modalAjuste') cerrarModal(); }

setTimeout(() => {
    document.querySelectorAll('.alert-custom').forEach(a => a.style.opacity = '0');
}, 3500);
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

.inventario-container {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(145deg, #e8f4f8 0%, #d0eaf0 100%);
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    padding: 20px 0;
    overflow-x: hidden;
}

/* Burbujas */
.bg-elements { position: fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:0; }
.bubble { position:absolute; border-radius:50%; opacity:.07; animation:floatBubble 18s infinite ease-in-out; background:var(--azul_2); }
.b1 { width:300px; height:300px; top:-80px; left:-80px; }
.b2 { width:200px; height:200px; bottom:10%; right:-60px; animation-delay:6s; }
.b3 { width:150px; height:150px; top:40%; left:5%; animation-delay:12s; }
@keyframes floatBubble {
    0%,100% { transform:translateY(0) scale(1); }
    50% { transform:translateY(-20px) scale(1.05); }
}

/* Alertas */
.alert-custom {
    padding:14px 20px; border-radius:12px; margin-bottom:20px;
    font-weight:600; display:flex; align-items:center; gap:10px;
    transition:opacity .5s ease; position:relative; z-index:10;
}
.alert-custom.success { background:var(--azul_3); color:var(--azul_2); border-left:5px solid var(--verde_azul); }
.alert-custom.error   { background:#fde8e8; color:#7b1d1d; border-left:5px solid #e53e3e; }

/* Header */
.inventario-header {
    background: linear-gradient(135deg, var(--azul_2) 0%, var(--azul_1) 100%);
    color: var(--blanco);
    padding: 25px 30px;
    display: flex; align-items: center; gap: 20px;
    border-radius: 24px 24px 0 0;
    position: relative; overflow: hidden; z-index: 10;
}
.inventario-header::after {
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
    width:58px; height:58px;
    background:rgba(255,255,255,0.15); border-radius:18px;
    display:flex; align-items:center; justify-content:center; font-size:1.8rem;
}
.header-title h4 { margin:0; font-weight:700; font-size:1.4rem; }
.header-title p  { margin:4px 0 0; opacity:.85; font-size:.88rem; }
.header-deco { font-size:1.4rem; }
.header-deco span { margin:0 4px; display:inline-block; animation:bounce 2s infinite; }
.header-deco span:nth-child(2) { animation-delay:.3s; }
.header-deco span:nth-child(3) { animation-delay:.6s; }
@keyframes bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }

/* Tarjeta */
.inventario-card {
    background: rgba(255,255,255,0.97);
    border-radius: 0 0 24px 24px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(19,45,70,0.12);
    position: relative; z-index: 10;
}

/* Resumen */
.inventario-resumen {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px; margin-bottom: 30px;
}
.resumen-card {
    background: linear-gradient(145deg, #ffffff, #e8f4f8);
    padding: 25px; border-radius: 20px; text-align: center;
    box-shadow: 0 5px 15px rgba(19,45,70,0.07);
    border: 1px solid rgba(69,123,157,0.15);
    transition: all .3s ease;
}
.resumen-card:hover { transform:translateY(-5px); box-shadow:0 15px 30px rgba(19,45,70,0.15); }
.resumen-card i { font-size:2.5rem; color:var(--azul_1); margin-bottom:15px; display:block; }
.resumen-card.warning i { color:#e53e3e; }
.resumen-valor { display:block; font-size:2rem; font-weight:700; color:var(--azul_2); }
.resumen-label { color:var(--azul_1); font-size:.9rem; text-transform:uppercase; letter-spacing:.5px; }

/* Tabla */
.inventario-table { width:100%; border-collapse:separate; border-spacing:0 10px; }
.inventario-table thead th {
    background: linear-gradient(145deg, #e8f4f8, #d0eaf0);
    color: var(--azul_2); padding:15px; font-weight:700; border:none;
    font-size:.88rem; text-transform:uppercase; letter-spacing:.4px;
}
.inventario-table thead th:first-child { border-radius:12px 0 0 12px; }
.inventario-table thead th:last-child  { border-radius:0 12px 12px 0; }
.inventario-table tbody tr {
    background:white; box-shadow:0 3px 10px rgba(19,45,70,0.04);
    transition:all .3s ease;
}
.inventario-table tbody tr:hover { transform:translateY(-3px); box-shadow:0 10px 25px rgba(19,45,70,0.12); }
.inventario-table tbody td { padding:15px; vertical-align:middle; border:none; color:var(--negrito); }
.inventario-table tbody td:first-child { border-radius:12px 0 0 12px; }
.inventario-table tbody td:last-child  { border-radius:0 12px 12px 0; }

.producto-info-cell { display:flex; align-items:center; gap:12px; }
.producto-thumb { width:50px; height:50px; border-radius:10px; object-fit:cover; border:2px solid #fff; box-shadow:0 3px 8px rgba(0,0,0,.1); }
.producto-thumb-placeholder {
    width:50px; height:50px; border-radius:10px;
    background:var(--azul_3); display:flex; align-items:center; justify-content:center;
    color:var(--azul_2); font-size:1.4rem;
}
.precio-cell { font-weight:700; color:var(--azul_1); }

.stock-badge { display:inline-block; padding:7px 16px; border-radius:30px; font-weight:700; font-size:.88rem; }
.stock-badge.normal { background:#d0f4ee; color:#065f46; }
.stock-badge.bajo   { background:var(--amarillo_claro); color:#7a6000; }

.badge-estado { display:inline-block; padding:5px 13px; border-radius:20px; font-size:.83rem; font-weight:700; }
.badge-estado.normal  { background:var(--verde_azul); color:var(--azul_2); }
.badge-estado.bajo    { background:var(--amarillo); color:var(--azul_2); }
.badge-estado.agotado { background:#e53e3e; color:white; }

.btn-ajustar {
    background: linear-gradient(145deg, var(--azul_2), var(--azul_1));
    color:white; border:none; padding:8px 16px; border-radius:10px;
    font-size:.9rem; cursor:pointer; transition:all .3s ease;
    display:inline-flex; align-items:center; gap:6px;
}
.btn-ajustar:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(19,45,70,0.3); }

.empty-state { text-align:center; padding:40px; }
.btn-crear-producto {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color:white; padding:12px 25px; border-radius:50px;
    text-decoration:none; font-weight:600; margin-top:15px; transition:all .3s ease;
}
.btn-crear-producto:hover { transform:translateY(-3px); box-shadow:0 10px 25px rgba(19,45,70,0.3); color:white; }

/* Modal */
.modal-confirm {
    display:none; position:fixed; top:0; left:0;
    width:100%; height:100%;
    background:rgba(19,45,70,0.55); backdrop-filter:blur(5px);
    z-index:1000; align-items:center; justify-content:center;
}
.modal-content {
    background:white; border-radius:24px; overflow:hidden;
    box-shadow:0 30px 60px rgba(19,45,70,0.3);
    animation:slideUp .3s ease;
}
@keyframes slideUp {
    from { opacity:0; transform:translateY(50px); }
    to   { opacity:1; transform:translateY(0); }
}
.modal-header {
    background:linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color:var(--blanco); padding:20px; text-align:center; position:sticky; top:0; z-index:1;
}
.modal-icon { font-size:2.5rem; margin-bottom:8px; }
.modal-header h3 { margin:0; font-size:1.2rem; font-weight:700; }
.modal-body { padding:25px; }
.modal-footer {
    padding:18px 25px; display:flex; gap:10px; justify-content:flex-end;
    border-top:1px solid #e8f4f8; position:sticky; bottom:0; background:white; z-index:1;
}

/* Form */
.form-control {
    width:100%; padding:10px 15px;
    border:2px solid var(--azul_3); border-radius:10px;
    transition:all .3s ease; color:var(--negrito); font-size:.95rem;
}
.form-control:focus { outline:none; border-color:var(--azul_1); box-shadow:0 0 0 3px rgba(69,123,157,0.15); }
.form-control[readonly] { background:#f0f8fb; }
.form-label { font-weight:700; color:var(--azul_2); margin-bottom:5px; display:block; font-size:.9rem; }

.btn-cancel {
    background:#eef2f5; border:none; padding:10px 20px;
    border-radius:10px; color:#555; cursor:pointer; font-weight:600;
}
.btn-cancel:hover { background:#dde4ea; }
.btn-confirmar {
    background:linear-gradient(145deg, var(--azul_2), var(--azul_1));
    border:none; padding:10px 22px; border-radius:10px;
    color:white; cursor:pointer; font-weight:700; transition:all .3s;
}
.btn-confirmar:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(19,45,70,0.3); }

/* Lista insumos */
#insumos-lista {
    max-height:200px; overflow-y:auto;
    border:2px solid var(--azul_3); border-radius:10px; padding:10px;
}
.insumo-check-item {
    display:flex; align-items:center; gap:10px;
    padding:8px 10px; border-radius:8px; cursor:pointer;
    transition:background .2s; margin-bottom:4px;
}
.insumo-check-item:hover { background:#e8f4f8; }
.insumo-check-item input[type=checkbox] { width:16px; height:16px; accent-color:var(--azul_1); cursor:pointer; flex-shrink:0; }
.insumo-info { display:flex; justify-content:space-between; flex:1; align-items:center; }
.insumo-nombre { font-weight:600; color:var(--azul_2); font-size:.9rem; }
.insumo-stock { font-size:.82rem; padding:3px 10px; border-radius:20px; background:#d0f4ee; color:#065f46; font-weight:600; }
.insumo-stock.bajo { background:var(--amarillo_claro); color:#7a6000; }

#insumos-error {
    display:none; color:#e53e3e; font-size:.85rem; margin-top:6px;
}

@media (max-width:768px) {
    .inventario-header { flex-direction:column; text-align:center; padding:20px; border-radius:16px 16px 0 0; }
    .header-deco { margin:0 auto; }
    .inventario-card { padding:20px; }
    .inventario-table thead { display:none; }
    .inventario-table tbody tr { display:block; margin-bottom:15px; }
    .inventario-table tbody td {
        display:block; text-align:right; padding:10px 15px;
        position:relative; border-bottom:1px solid #e8f4f8;
        border-radius:0 !important;
    }
    .inventario-table tbody td:before {
        content:attr(data-label); position:absolute; left:15px;
        font-weight:700; color:var(--azul_2);
    }
    .producto-info-cell { justify-content:flex-end; }
    .modal-footer { flex-direction:column; }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

@endsection
