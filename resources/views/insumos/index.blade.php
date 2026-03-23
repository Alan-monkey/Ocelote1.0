@extends('layouts.app')
@section('content')

<div class="insumos-container">
    <div class="bg-elements">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <div class="container py-4">

        @if(session('success'))
            <div class="alert-custom success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-custom error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Header -->
        <div class="insumos-header">
            <div class="header-icon"><i class="fas fa-boxes"></i></div>
            <div class="header-title">
                <h4>Gestión de Insumos</h4>
                <p>Alta, baja y modificación de insumos</p>
            </div>
            <button class="btn-nuevo ms-auto" onclick="abrirModalCrear()">
                <i class="fas fa-plus"></i> Nuevo Insumo
            </button>
        </div>

        <!-- Tabla -->
        <div class="insumos-card">
            <div class="table-responsive">
                <table class="table insumos-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Mínimo</th>
                            <th>Estado</th>
                            <th>Última actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($insumos as $insumo)
                        <tr>
                            <td><strong>{{ $insumo->nombre }}</strong></td>
                            <td>{{ $insumo->descripcion }}</td>
                            <td><span class="badge-unidad">{{ $insumo->unidad_medida }}</span></td>
                            <td>
                                <span class="stock-badge {{ $insumo->cantidad <= $insumo->cantidad_minima ? 'bajo' : 'normal' }}">
                                    {{ $insumo->cantidad }}
                                </span>
                            </td>
                            <td>{{ $insumo->cantidad_minima }}</td>
                            <td>
                                @if($insumo->cantidad == 0)
                                    <span class="badge-estado agotado">Agotado</span>
                                @elseif($insumo->cantidad <= $insumo->cantidad_minima)
                                    <span class="badge-estado bajo">Stock bajo</span>
                                @else
                                    <span class="badge-estado normal">Normal</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($insumo->fecha_actualizacion))
                                    {{ \Carbon\Carbon::parse($insumo->fecha_actualizacion)->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn-accion editar" onclick="abrirModalEditar(
                                    '{{ $insumo->_id }}',
                                    '{{ addslashes($insumo->nombre) }}',
                                    '{{ addslashes($insumo->descripcion) }}',
                                    '{{ $insumo->unidad_medida }}',
                                    {{ $insumo->cantidad }},
                                    {{ $insumo->cantidad_minima }}
                                )"><i class="fas fa-edit"></i></button>
                                <button class="btn-accion eliminar" onclick="abrirModalEliminar('{{ $insumo->_id }}', '{{ addslashes($insumo->nombre) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-box-open fa-3x mb-3" style="color:#457b9d; opacity:.4;"></i>
                                <p style="color:#457b9d;">No hay insumos registrados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div id="modalCrear" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head"><i class="fas fa-plus-circle"></i> Nuevo Insumo</div>
        <form action="{{ route('insumos.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción *</label>
                    <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Unidad de medida *</label>
                    <input type="text" name="unidad_medida" class="form-control" placeholder="Ej: Piezas, Litros, Kg" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" name="cantidad" class="form-control" min="0" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Cantidad mínima *</label>
                        <input type="number" name="cantidad_minima" class="form-control" min="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="cerrarModales()">Cancelar</button>
                <button type="submit" class="btn-confirmar"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<div id="modalEditar" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head"><i class="fas fa-edit"></i> Editar Insumo</div>
        <form id="formEditar" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción *</label>
                    <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Unidad de medida *</label>
                    <input type="text" name="unidad_medida" id="edit_unidad" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" name="cantidad" id="edit_cantidad" class="form-control" min="0" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Cantidad mínima *</label>
                        <input type="number" name="cantidad_minima" id="edit_cantidad_minima" class="form-control" min="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="cerrarModales()">Cancelar</button>
                <button type="submit" class="btn-confirmar"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="modalEliminar" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-head danger"><i class="fas fa-trash"></i> Eliminar Insumo</div>
        <form action="{{ route('insumos.destroy') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="delete_id">
            <div class="modal-body text-center">
                <p style="color:#132d46; font-size:1rem;">¿Seguro que deseas eliminar <strong id="delete_nombre"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="cerrarModales()">Cancelar</button>
                <button type="submit" class="btn-danger-confirm"><i class="fas fa-trash"></i> Eliminar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalCrear() {
    document.getElementById('modalCrear').style.display = 'flex';
}
function abrirModalEditar(id, nombre, descripcion, unidad, cantidad, cantidad_minima) {
    document.getElementById('formEditar').action = '/insumos/' + id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_descripcion').value = descripcion;
    document.getElementById('edit_unidad').value = unidad;
    document.getElementById('edit_cantidad').value = cantidad;
    document.getElementById('edit_cantidad_minima').value = cantidad_minima;
    document.getElementById('modalEditar').style.display = 'flex';
}
function abrirModalEliminar(id, nombre) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_nombre').textContent = nombre;
    document.getElementById('modalEliminar').style.display = 'flex';
}
function cerrarModales() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
}
window.onclick = e => { if (e.target.classList.contains('modal-overlay')) cerrarModales(); }

// Auto-cerrar alertas
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

.insumos-container {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(145deg, #e8f4f8 0%, #d0eaf0 100%);
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    padding: 20px 0;
    overflow-x: hidden;
}

/* Burbujas decorativas */
.bg-elements { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
.bubble {
    position: absolute;
    border-radius: 50%;
    opacity: 0.07;
    animation: floatBubble 18s infinite ease-in-out;
    background: var(--azul_2);
}
.b1 { width: 300px; height: 300px; top: -80px; left: -80px; animation-delay: 0s; }
.b2 { width: 200px; height: 200px; bottom: 10%; right: -60px; animation-delay: 6s; }
.b3 { width: 150px; height: 150px; top: 40%; left: 5%; animation-delay: 12s; }
@keyframes floatBubble {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-20px) scale(1.05); }
}

/* Alertas */
.alert-custom {
    padding: 14px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: opacity 0.5s ease;
    position: relative;
    z-index: 10;
}
.alert-custom.success { background: var(--azul_3); color: var(--azul_2); border-left: 5px solid var(--verde_azul); }
.alert-custom.error   { background: #fde8e8; color: #7b1d1d; border-left: 5px solid #e53e3e; }

/* Header */
.insumos-header {
    background: linear-gradient(135deg, var(--azul_2) 0%, var(--azul_1) 100%);
    color: var(--blanco);
    padding: 25px 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    border-radius: 24px 24px 0 0;
    position: relative;
    overflow: hidden;
    z-index: 10;
}
.insumos-header::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 180px; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08));
    transform: skewX(-20deg) translateX(80px);
    animation: shine 4s infinite;
}
@keyframes shine {
    0%   { transform: skewX(-20deg) translateX(80px); }
    20%  { transform: skewX(-20deg) translateX(-220px); }
    100% { transform: skewX(-20deg) translateX(-220px); }
}
.header-icon {
    width: 58px; height: 58px;
    background: rgba(255,255,255,0.15);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
}
.header-title h4 { margin: 0; font-weight: 700; font-size: 1.4rem; }
.header-title p  { margin: 4px 0 0; opacity: 0.85; font-size: 0.88rem; }

.btn-nuevo {
    background: var(--verde_azul);
    border: none;
    color: var(--azul_2);
    padding: 10px 22px;
    border-radius: 50px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex; align-items: center; gap: 8px;
    white-space: nowrap;
}
.btn-nuevo:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(7,205,175,0.4); }

/* Tarjeta tabla */
.insumos-card {
    background: rgba(255,255,255,0.97);
    border-radius: 0 0 24px 24px;
    padding: 28px;
    box-shadow: 0 20px 40px rgba(19,45,70,0.12);
    position: relative;
    z-index: 10;
}

/* Tabla */
.insumos-table { border-collapse: separate; border-spacing: 0 10px; width: 100%; }
.insumos-table thead th {
    background: linear-gradient(145deg, #e8f4f8, #d0eaf0);
    color: var(--azul_2);
    padding: 14px 16px;
    font-weight: 700;
    border: none;
    font-size: 0.88rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.insumos-table thead th:first-child { border-radius: 12px 0 0 12px; }
.insumos-table thead th:last-child  { border-radius: 0 12px 12px 0; }

.insumos-table tbody tr {
    background: white;
    box-shadow: 0 3px 12px rgba(19,45,70,0.05);
    transition: all 0.3s;
}
.insumos-table tbody tr:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(19,45,70,0.12);
}
.insumos-table tbody td {
    padding: 14px 16px;
    border: none;
    vertical-align: middle;
    color: var(--negrito);
}
.insumos-table tbody td:first-child { border-radius: 12px 0 0 12px; }
.insumos-table tbody td:last-child  { border-radius: 0 12px 12px 0; }

/* Badges */
.badge-unidad {
    background: var(--azul_3);
    color: var(--azul_2);
    padding: 5px 13px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 700;
}
.stock-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.9rem;
}
.stock-badge.normal { background: #d0f4ee; color: #065f46; }
.stock-badge.bajo   { background: var(--amarillo_claro); color: #7a6000; }

.badge-estado {
    display: inline-block;
    padding: 4px 13px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 700;
}
.badge-estado.normal  { background: var(--verde_azul); color: var(--azul_2); }
.badge-estado.bajo    { background: var(--amarillo); color: var(--azul_2); }
.badge-estado.agotado { background: #e53e3e; color: white; }

/* Botones acción */
.btn-accion {
    border: none;
    padding: 7px 13px;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.3s;
    margin: 2px;
    font-size: 0.9rem;
}
.btn-accion.editar   { background: var(--azul_1); color: white; }
.btn-accion.eliminar { background: #e53e3e; color: white; }
.btn-accion:hover { transform: translateY(-2px); box-shadow: 0 5px 14px rgba(0,0,0,0.18); }

/* Modales */
.modal-overlay {
    display: none;
    position: fixed; top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(19,45,70,0.55);
    backdrop-filter: blur(5px);
    z-index: 1000;
    align-items: center; justify-content: center;
}
.modal-box {
    background: white;
    border-radius: 22px;
    overflow: hidden;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 30px 60px rgba(19,45,70,0.3);
    animation: slideUp 0.3s ease;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}
.modal-head {
    background: linear-gradient(135deg, var(--azul_2), var(--azul_1));
    color: var(--blanco);
    padding: 20px 25px;
    font-size: 1.05rem;
    font-weight: 700;
    display: flex; align-items: center; gap: 10px;
}
.modal-head.danger { background: linear-gradient(135deg, #7b1d1d, #e53e3e); }
.modal-body { padding: 25px; }
.modal-foot {
    padding: 15px 25px;
    display: flex; gap: 10px; justify-content: flex-end;
    border-top: 1px solid #e8f4f8;
}

/* Form controls */
.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid var(--azul_3);
    border-radius: 10px;
    transition: all 0.3s;
    color: var(--negrito);
    font-size: 0.95rem;
}
.form-control:focus {
    outline: none;
    border-color: var(--azul_1);
    box-shadow: 0 0 0 3px rgba(69,123,157,0.15);
}
.form-label { font-weight: 600; color: var(--azul_2); margin-bottom: 5px; display: block; font-size: 0.9rem; }

/* Botones modal */
.btn-cancel {
    background: #eef2f5; border: none;
    padding: 10px 20px; border-radius: 10px;
    color: #555; cursor: pointer; font-weight: 600;
    transition: background 0.2s;
}
.btn-cancel:hover { background: #dde4ea; }
.btn-confirmar {
    background: linear-gradient(145deg, var(--azul_2), var(--azul_1));
    border: none; padding: 10px 22px;
    border-radius: 10px; color: white;
    cursor: pointer; font-weight: 700;
    transition: all 0.3s;
}
.btn-confirmar:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(19,45,70,0.3); }
.btn-danger-confirm {
    background: linear-gradient(145deg, #7b1d1d, #e53e3e);
    border: none; padding: 10px 22px;
    border-radius: 10px; color: white;
    cursor: pointer; font-weight: 700;
    transition: all 0.3s;
}
.btn-danger-confirm:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(229,62,62,0.35); }

@media (max-width: 768px) {
    .insumos-header { flex-direction: column; text-align: center; padding: 20px; border-radius: 16px 16px 0 0; }
    .btn-nuevo { margin: 10px auto 0; }
    .insumos-table thead { display: none; }
    .insumos-table tbody tr { display: block; margin-bottom: 15px; border-radius: 12px; }
    .insumos-table tbody td {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 15px; border-bottom: 1px solid #e8f4f8;
        border-radius: 0;
    }
    .insumos-table tbody td:first-child { border-radius: 12px 12px 0 0; }
    .insumos-table tbody td:last-child  { border-radius: 0 0 12px 12px; border-bottom: none; }
    .insumos-table tbody td::before {
        content: attr(data-label);
        font-weight: 700;
        color: var(--azul_2);
        font-size: 0.82rem;
        text-transform: uppercase;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

@endsection
