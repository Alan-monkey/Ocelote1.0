@extends('layouts.app')
@section('content')

<style>
:root {
    --azul_1: #457b9d;
    --azul_2: #132d46;
    --azul_3: #a8dadc;
    --blanco: #f1faee;
    --verde_azul: #07cdaf;
    --amarillo_claro: #fffaca;
}
.rutas-container {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(145deg, #e8f4f8 0%, #d0eaf0 100%);
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    padding: 20px 0;
    overflow-x: hidden;
}
.bg-elements { position: fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:0; }
.bubble { position:absolute; border-radius:50%; opacity:.07; animation:floatBubble 18s infinite ease-in-out; background:var(--azul_2); }
.b1 { width:300px; height:300px; top:-80px; left:-80px; }
.b2 { width:200px; height:200px; bottom:10%; right:-60px; animation-delay:6s; }
.b3 { width:150px; height:150px; top:40%; left:5%; animation-delay:12s; }
@keyframes floatBubble { 0%,100%{transform:translateY(0) scale(1);} 50%{transform:translateY(-20px) scale(1.05);} }

.alert-custom { padding:14px 20px; border-radius:12px; margin-bottom:20px; font-weight:600; display:flex; align-items:center; gap:10px; transition:opacity .5s ease; position:relative; z-index:10; }
.alert-custom.success { background:var(--azul_3); color:var(--azul_2); border-left:5px solid var(--verde_azul); }
.alert-custom.error   { background:#fde8e8; color:#7b1d1d; border-left:5px solid #e53e3e; }

.rutas-header {
    background: linear-gradient(135deg, var(--azul_2) 0%, var(--azul_1) 100%);
    color: var(--blanco);
    padding: 25px 30px;
    display: flex; align-items: center; gap: 20px;
    border-radius: 24px 24px 0 0;
    position: relative; overflow: hidden; z-index: 10;
}
.rutas-header::after {
    content:''; position:absolute; top:0; right:0;
    width:180px; height:100%;
    background:linear-gradient(90deg, transparent, rgba(255,255,255,0.08));
    transform:skewX(-20deg) translateX(80px);
    animation:shine 4s infinite;
}
@keyframes shine { 0%{transform:skewX(-20deg) translateX(80px);} 20%,100%{transform:skewX(-20deg) translateX(-220px);} }
.header-icon { width:58px; height:58px; background:rgba(255,255,255,0.15); border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; }
.header-title h4 { margin:0; font-weight:700; font-size:1.4rem; }
.header-title p  { margin:4px 0 0; opacity:.85; font-size:.88rem; }

.rutas-card {
    background: rgba(255,255,255,0.97);
    border-radius: 0 0 24px 24px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(19,45,70,0.12);
    position: relative; z-index: 10;
    margin-bottom: 24px;
}
.rutas-card h5 { color:var(--azul_2); font-weight:700; margin-bottom:16px; border-bottom:2px solid var(--azul_3); padding-bottom:8px; }

.btn-nuevo { background:linear-gradient(135deg, var(--verde_azul), var(--azul_1)); color:white; border:none; border-radius:10px; padding:10px 20px; font-weight:600; cursor:pointer; transition:all 0.2s; }
.btn-nuevo:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(7,205,175,0.4); }

.table-rutas { width:100%; border-collapse:separate; border-spacing:0 8px; }
.table-rutas thead th { background:linear-gradient(145deg, #e8f4f8, #d0eaf0); color:var(--azul_2); padding:14px; font-weight:700; border:none; font-size:.88rem; text-transform:uppercase; letter-spacing:.4px; }
.table-rutas thead th:first-child { border-radius:12px 0 0 12px; }
.table-rutas thead th:last-child  { border-radius:0 12px 12px 0; }
.table-rutas tbody tr { background:white; box-shadow:0 3px 10px rgba(19,45,70,0.04); transition:all .3s ease; }
.table-rutas tbody tr:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(19,45,70,0.1); }
.table-rutas tbody td { padding:14px; vertical-align:middle; border:none; }
.table-rutas tbody td:first-child { border-radius:12px 0 0 12px; }
.table-rutas tbody td:last-child  { border-radius:0 12px 12px 0; }

.badge-dia { background:var(--azul_3); color:var(--azul_2); padding:5px 12px; border-radius:20px; font-size:.82rem; font-weight:700; }

.cliente-item { display:flex; align-items:center; gap:10px; background:#f0f8fb; border-radius:10px; padding:10px 14px; margin-bottom:8px; cursor:grab; border:2px solid transparent; transition:all 0.2s; }
.cliente-item:hover { border-color:var(--azul_1); }
.cliente-item.dragging { opacity:0.5; border-color:var(--verde_azul); }
.orden-badge { background:var(--azul_2); color:white; border-radius:50%; width:28px; height:28px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; }
.btn-remove-cliente { background:none; border:none; color:#ef4444; cursor:pointer; padding:2px 6px; border-radius:6px; transition:background 0.2s; }
.btn-remove-cliente:hover { background:#fee2e2; }

.search-box { position:relative; }
.search-box input { padding-left:36px; border-radius:10px; border:2px solid #d0eaf0; transition:border 0.2s; width:100%; padding-top:10px; padding-bottom:10px; }
.search-box input:focus { border-color:var(--azul_1); outline:none; box-shadow:none; }
.search-box i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; }

.cliente-check-item { display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:8px; cursor:pointer; transition:background 0.15s; }
.cliente-check-item:hover { background:#e8f4f8; }
.cliente-check-item input[type=checkbox] { width:18px; height:18px; accent-color:var(--azul_1); cursor:pointer; }
.clientes-lista-scroll { max-height:260px; overflow-y:auto; border:2px solid #d0eaf0; border-radius:10px; padding:8px; }

.form-label { font-weight:700; color:var(--azul_2); margin-bottom:5px; display:block; font-size:.9rem; }
.form-select, .form-control { border:2px solid var(--azul_3); border-radius:10px; padding:10px 15px; transition:all .3s; }
.form-select:focus, .form-control:focus { border-color:var(--azul_1); box-shadow:0 0 0 3px rgba(69,123,157,0.15); }
</style>

<div class="rutas-container">
    <div class="bg-elements">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <div class="container py-4" style="position:relative;z-index:1;">

        @if(session('success'))
            <div class="alert-custom success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-custom error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- HEADER + TABLA en una sola card --}}
        <div>
            <div class="rutas-header">
                <div class="header-icon"><i class="fas fa-route"></i></div>
                <div class="header-title">
                    <h4>Rutas de Reparto</h4>
                    <p>Asignación de clientes a repartidores por día</p>
                </div>
                <div class="ms-auto">
                    <button class="btn-nuevo" onclick="abrirModalRuta()">
                        <i class="fas fa-plus"></i> Nueva Ruta
                    </button>
                </div>
            </div>

            <div class="rutas-card">
                <h5><i class="fas fa-list-ul me-2"></i>Rutas Registradas</h5>
                <div class="table-responsive">
                    <table class="table table-rutas">
                        <thead>
                            <tr>
                                        <th>Título</th>
                                <th>Repartidor</th>
                                <th>Día de Reparto</th>
                                <th>Clientes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rutas as $ruta)
                            <tr>
                                <td><strong>{{ $ruta->titulo ?? '—' }}</strong></td>
                                <td><strong>{{ $ruta->repartidor_nombre ?? '—' }}</strong></td>
                                <td><span class="badge-dia">{{ $ruta->dia_reparto ?? '—' }}</span></td>
                                <td>
                                    @if(!empty($ruta->clientes))
                                        @foreach($ruta->clientes as $i => $c)
                                            <small>{{ ($i+1) }}. {{ is_array($c) ? $c['nombre'] : $c }}</small><br>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Sin clientes</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                        onclick="editarRuta('{{ $ruta->_id }}', {{ json_encode($ruta) }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('rutas.destroy') }}" class="d-inline"
                                        onsubmit="return confirm('¿Eliminar esta ruta?')">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $ruta->_id }}">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-route fa-3x mb-3" style="color:#a8dadc;opacity:.6;"></i>
                                    <h6 style="color:#132d46;">No hay rutas registradas</h6>
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

{{-- ===== MODAL NUEVA/EDITAR RUTA ===== --}}
<div class="modal fade" id="modalRuta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--azul_2),var(--azul_1));color:white;">
                <h5 class="modal-title" id="modalRutaTitulo"><i class="fas fa-route me-2"></i>Nueva Ruta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRuta" method="POST" action="{{ route('rutas.store') }}">
                @csrf
                <input type="hidden" name="_method" id="rutaMethod" value="POST">
                <input type="hidden" name="id" id="rutaId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Título de la Ruta</label>
                            <input type="text" class="form-control" name="titulo" id="rutaTitulo"
                                placeholder="Ej: Ruta Centro Lunes" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Repartidor</label>
                            <select class="form-select" id="selectRepartidor" name="repartidor_id" required onchange="onRepartidorChange(this)">
                                <option value="">— Selecciona un repartidor —</option>
                                @foreach($repartidores as $rep)
                                    <option value="{{ $rep->_id }}" data-nombre="{{ $rep->nombre }}">
                                        {{ $rep->nombre }} — {{ $rep->telefono ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="repartidor_nombre" id="repartidorNombre">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Día de Reparto</label>
                            <select class="form-select" name="dia_reparto" id="diaReparto" required>
                                <option value="">— Selecciona un día —</option>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Sábado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Agregar Clientes</label>
                            <div class="search-box mb-2">
                                <i class="fas fa-search"></i>
                                <input type="text" id="buscarCliente" placeholder="Buscar cliente..." oninput="filtrarClientes(this.value)">
                            </div>
                            <div class="clientes-lista-scroll" id="listaClientesCheck">
                                @foreach($clientes as $cliente)
                                <label class="cliente-check-item">
                                    <input type="checkbox" value="{{ $cliente->_id }}"
                                        data-nombre="{{ $cliente->nombre }}"
                                        data-dir="{{ $cliente->direccion ?? '' }}"
                                        onchange="toggleClienteSeleccionado(this)">
                                    <div>
                                        <div class="fw-semibold" style="color:var(--azul_2);">{{ $cliente->nombre }}</div>
                                        <small class="text-muted">{{ $cliente->direccion ?? '' }}</small>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12" id="seccionOrden" style="display:none;">
                            <label class="form-label">
                                <i class="fas fa-sort me-1"></i>Orden de Visita
                                <small class="text-muted fw-normal">(arrastra para reordenar)</small>
                            </label>
                            <div id="listaOrdenada"></div>
                        </div>
                        <div id="clientesHidden"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-nuevo px-4">
                        <i class="fas fa-save me-1"></i> Guardar Ruta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let clientesSeleccionados = [];
let dragSrc = null;

function abrirModalRuta() {
    document.getElementById('modalRutaTitulo').innerHTML = '<i class="fas fa-route me-2"></i>Nueva Ruta';
    document.getElementById('formRuta').action = '{{ route("rutas.store") }}';
    document.getElementById('rutaMethod').value = 'POST';
    document.getElementById('rutaId').value = '';
    document.getElementById('rutaTitulo').value = '';
    document.getElementById('selectRepartidor').value = '';
    document.getElementById('repartidorNombre').value = '';
    document.getElementById('diaReparto').value = '';
    clientesSeleccionados = [];
    resetCheckboxes();
    renderOrden();
    new bootstrap.Modal(document.getElementById('modalRuta')).show();
}

function editarRuta(id, ruta) {
    document.getElementById('modalRutaTitulo').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Ruta';
    document.getElementById('formRuta').action = '/rutas-reparto/' + id;
    document.getElementById('rutaMethod').value = 'PUT';
    document.getElementById('rutaId').value = id;
    document.getElementById('rutaTitulo').value = ruta.titulo ?? '';
    document.getElementById('selectRepartidor').value = ruta.repartidor_id ?? '';
    document.getElementById('repartidorNombre').value = ruta.repartidor_nombre ?? '';
    document.getElementById('diaReparto').value = ruta.dia_reparto ?? '';
    clientesSeleccionados = [];
    resetCheckboxes();
    if (ruta.clientes && ruta.clientes.length) {
        ruta.clientes.forEach(c => {
            const obj = typeof c === 'object' ? c : { id: c, nombre: c };
            clientesSeleccionados.push({ id: obj.id || obj._id, nombre: obj.nombre, orden: obj.orden || (clientesSeleccionados.length + 1) });
            const chk = document.querySelector(`#listaClientesCheck input[value="${obj.id || obj._id}"]`);
            if (chk) chk.checked = true;
        });
    }
    renderOrden();
    new bootstrap.Modal(document.getElementById('modalRuta')).show();
}

function onRepartidorChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('repartidorNombre').value = opt.dataset.nombre || '';
}

function filtrarClientes(q) {
    document.querySelectorAll('#listaClientesCheck .cliente-check-item').forEach(item => {
        item.style.display = item.innerText.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
}

function toggleClienteSeleccionado(chk) {
    const id = chk.value, nombre = chk.dataset.nombre;
    if (chk.checked) {
        if (!clientesSeleccionados.find(c => c.id === id))
            clientesSeleccionados.push({ id, nombre, orden: clientesSeleccionados.length + 1 });
    } else {
        clientesSeleccionados = clientesSeleccionados.filter(c => c.id !== id);
        recalcularOrden();
    }
    renderOrden();
}

function resetCheckboxes() {
    document.querySelectorAll('#listaClientesCheck input[type=checkbox]').forEach(c => c.checked = false);
}

function recalcularOrden() {
    clientesSeleccionados.forEach((c, i) => c.orden = i + 1);
}

function renderOrden() {
    const contenedor = document.getElementById('listaOrdenada');
    const seccion = document.getElementById('seccionOrden');
    const hidden = document.getElementById('clientesHidden');
    contenedor.innerHTML = '';
    hidden.innerHTML = '';
    if (clientesSeleccionados.length === 0) { seccion.style.display = 'none'; return; }
    seccion.style.display = '';
    clientesSeleccionados.forEach((c, i) => {
        const div = document.createElement('div');
        div.className = 'cliente-item';
        div.draggable = true;
        div.dataset.index = i;
        div.innerHTML = `<span class="orden-badge">${i+1}</span><span class="flex-grow-1 fw-semibold">${c.nombre}</span><button type="button" class="btn-remove-cliente" onclick="quitarCliente('${c.id}')"><i class="fas fa-times"></i></button>`;
        div.addEventListener('dragstart', onDragStart);
        div.addEventListener('dragover', onDragOver);
        div.addEventListener('drop', onDrop);
        div.addEventListener('dragend', onDragEnd);
        contenedor.appendChild(div);
        hidden.innerHTML += `<input type="hidden" name="clientes[${i}][id]" value="${c.id}"><input type="hidden" name="clientes[${i}][nombre]" value="${c.nombre}"><input type="hidden" name="clientes[${i}][orden]" value="${i+1}">`;
    });
}

function quitarCliente(id) {
    clientesSeleccionados = clientesSeleccionados.filter(c => c.id !== id);
    recalcularOrden();
    const chk = document.querySelector(`#listaClientesCheck input[value="${id}"]`);
    if (chk) chk.checked = false;
    renderOrden();
}

function onDragStart(e) { dragSrc = this; this.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; }
function onDragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; }
function onDrop(e) {
    e.preventDefault();
    if (dragSrc === this) return;
    const from = parseInt(dragSrc.dataset.index), to = parseInt(this.dataset.index);
    const moved = clientesSeleccionados.splice(from, 1)[0];
    clientesSeleccionados.splice(to, 0, moved);
    recalcularOrden();
    renderOrden();
}
function onDragEnd() { this.classList.remove('dragging'); }

setTimeout(() => {
    document.querySelectorAll('.alert-custom').forEach(a => a.style.opacity = '0');
}, 3500);
</script>

@endsection
