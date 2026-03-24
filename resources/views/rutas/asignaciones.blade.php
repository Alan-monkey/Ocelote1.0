@extends('layouts.app')
@section('content')

<style>
:root { --azul_1:#457b9d; --azul_2:#132d46; --azul_3:#a8dadc; --blanco:#f1faee; --verde_azul:#07cdaf; }
.page-container { min-height:100vh; background:linear-gradient(145deg,#e8f4f8,#d0eaf0); padding:20px 0; font-family:'Poppins','Segoe UI',sans-serif; }
.card-main { background:rgba(255,255,255,.97); border-radius:24px; box-shadow:0 20px 40px rgba(19,45,70,.12); overflow:hidden; margin-bottom:24px; }
.card-header-custom { background:linear-gradient(135deg,var(--azul_2),var(--azul_1)); color:var(--blanco); padding:22px 28px; display:flex; align-items:center; gap:16px; }
.header-icon { width:52px; height:52px; background:rgba(255,255,255,.15); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.6rem; }
.card-body-custom { padding:28px; }
.alert-custom { padding:14px 20px; border-radius:12px; margin-bottom:20px; font-weight:600; display:flex; align-items:center; gap:10px; }
.alert-custom.success { background:var(--azul_3); color:var(--azul_2); border-left:5px solid var(--verde_azul); }
.alert-custom.error   { background:#fde8e8; color:#7b1d1d; border-left:5px solid #e53e3e; }
.btn-primary-custom { background:linear-gradient(135deg,var(--verde_azul),var(--azul_1)); color:white; border:none; border-radius:10px; padding:10px 22px; font-weight:600; cursor:pointer; transition:all .2s; }
.btn-primary-custom:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(7,205,175,.4); }
.dia-filter-btn { border:2px solid var(--azul_3); background:white; color:var(--azul_2); border-radius:20px; padding:6px 18px; font-weight:600; cursor:pointer; transition:all .2s; margin:3px; }
.dia-filter-btn.active, .dia-filter-btn:hover { background:var(--azul_2); color:white; border-color:var(--azul_2); }
.ruta-card { border:2px solid #e8f4f8; border-radius:14px; padding:18px; margin-bottom:12px; transition:all .2s; }
.ruta-card:hover { border-color:var(--azul_1); box-shadow:0 4px 15px rgba(19,45,70,.08); }
.badge-dia { background:var(--azul_3); color:var(--azul_2); padding:4px 12px; border-radius:20px; font-size:.8rem; font-weight:700; }
.badge-estado-activa    { background:#d1fae5; color:#065f46; padding:4px 12px; border-radius:20px; font-size:.8rem; font-weight:700; }
.badge-estado-finalizada { background:#e5e7eb; color:#374151; padding:4px 12px; border-radius:20px; font-size:.8rem; font-weight:700; }
.garrafon-row { display:flex; align-items:center; gap:10px; background:#f0f8fb; border-radius:10px; padding:10px 14px; margin-bottom:8px; }
.garrafon-row input[type=number] { width:90px; border:2px solid var(--azul_3); border-radius:8px; padding:6px 10px; font-weight:600; }
.table-asig { width:100%; border-collapse:separate; border-spacing:0 6px; }
.table-asig thead th { background:linear-gradient(145deg,#e8f4f8,#d0eaf0); color:var(--azul_2); padding:12px; font-weight:700; border:none; font-size:.85rem; text-transform:uppercase; }
.table-asig thead th:first-child { border-radius:10px 0 0 10px; }
.table-asig thead th:last-child  { border-radius:0 10px 10px 0; }
.table-asig tbody tr { background:white; box-shadow:0 2px 8px rgba(19,45,70,.04); }
.table-asig tbody td { padding:12px; vertical-align:middle; border:none; }
.table-asig tbody td:first-child { border-radius:10px 0 0 10px; }
.table-asig tbody td:last-child  { border-radius:0 10px 10px 0; }
</style>

<div class="page-container">
<div class="container py-4">

    @if(session('success'))
        <div class="alert-custom success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-custom error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="card-main">
        <div class="card-header-custom">
            <div class="header-icon"><i class="fas fa-truck-loading"></i></div>
            <div>
                <h4 class="mb-0 fw-700">Asignación de Rutas</h4>
                <small style="opacity:.85;">Habilita rutas y descuenta inventario automáticamente</small>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('rutas.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Rutas
                </a>
                <button class="btn-primary-custom" onclick="abrirModalAsignacion()">
                    <i class="fas fa-plus me-1"></i> Nueva Asignación
                </button>
            </div>
        </div>

        <div class="card-body-custom">
            {{-- Filtro por día --}}
            <div class="mb-4">
                <span class="fw-bold me-2" style="color:var(--azul_2);">Filtrar por día:</span>
                <button class="dia-filter-btn active" onclick="filtrarDia('todos', this)">Todos</button>
                @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia)
                    <button class="dia-filter-btn" onclick="filtrarDia('{{ $dia }}', this)">{{ $dia }}</button>
                @endforeach
            </div>

            {{-- Tabla de asignaciones --}}
            <div class="table-responsive">
                <table class="table table-asig">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Día</th>
                            <th>Garrafones Asignados</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAsignaciones">
                        @forelse($asignaciones as $asig)
                        <tr data-dia="{{ $asig->dia ?? '' }}">
                            <td><strong>{{ $asig->titulo ?? '—' }}</strong></td>
                            <td><span class="badge-dia">{{ $asig->dia ?? '—' }}</span></td>
                            <td>
                                @if(!empty($asig->garrafones))
                                    @foreach($asig->garrafones as $g)
                                        <small><i class="fas fa-tint me-1" style="color:var(--azul_1);"></i>
                                            {{ is_array($g) ? $g['nombre'] : $g }}:
                                            <strong>{{ is_array($g) ? $g['cantidad'] : '?' }}</strong>
                                        </small><br>
                                    @endforeach
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-estado-{{ $asig->estado ?? 'activa' }}">
                                    {{ ucfirst($asig->estado ?? 'activa') }}
                                </span>
                            </td>
                            <td><small>{{ isset($asig->fecha_asignacion) ? \Carbon\Carbon::parse($asig->fecha_asignacion)->format('d/m/Y H:i') : '—' }}</small></td>
                            <td>
                                @if(($asig->estado ?? 'activa') === 'activa')
                                <form method="POST" action="{{ route('rutas.asignaciones.finalizar') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $asig->_id }}">
                                    <button class="btn btn-sm btn-outline-success" title="Finalizar ruta">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('rutas.asignaciones.destroy') }}" class="d-inline"
                                    onsubmit="return confirm('¿Eliminar esta asignación?')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $asig->_id }}">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr id="sinRegistros">
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-truck-loading fa-3x mb-3" style="color:#a8dadc;opacity:.5;"></i>
                                <h6 style="color:#132d46;">No hay asignaciones registradas</h6>
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

{{-- ===== MODAL NUEVA ASIGNACIÓN ===== --}}
<div class="modal fade" id="modalAsignacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--azul_2),var(--azul_1));color:white;">
                <h5 class="modal-title"><i class="fas fa-truck-loading me-2"></i>Nueva Asignación de Ruta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('rutas.asignaciones.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Selector de día --}}
                        <div class="col-md-5">
                            <label class="form-label fw-bold" style="color:var(--azul_2);">Día de Reparto</label>
                            <select class="form-select" id="selectDiaModal" onchange="cargarRutasDia(this.value)" required>
                                <option value="">— Selecciona un día —</option>
                                @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia)
                                    <option value="{{ $dia }}">{{ $dia }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Selector de ruta del día --}}
                        <div class="col-md-7">
                            <label class="form-label fw-bold" style="color:var(--azul_2);">Ruta</label>
                            <select class="form-select" id="selectRutaModal" name="ruta_id" required onchange="onRutaChange(this)">
                                <option value="">— Primero selecciona un día —</option>
                            </select>
                            <input type="hidden" name="titulo" id="inputTitulo">
                            <input type="hidden" name="dia"    id="inputDia">
                        </div>

                        {{-- Garrafones --}}
                        <div class="col-12">
                            <label class="form-label fw-bold" style="color:var(--azul_2);">
                                <i class="fas fa-tint me-1"></i>Garrafones a Asignar
                            </label>
                            <div id="garrafonesContainer">
                                @foreach($inventario as $inv)
                                @php $prod = $inv->producto ?? null; @endphp
                                @if($prod)
                                <div class="garrafon-row">
                                    <input type="hidden" name="garrafones[{{ $loop->index }}][producto_id]" value="{{ $inv->producto_id }}">
                                    <input type="hidden" name="garrafones[{{ $loop->index }}][nombre]"      value="{{ $prod['nombre'] ?? 'Producto' }}">
                                    <i class="fas fa-tint" style="color:var(--azul_1);font-size:1.2rem;"></i>
                                    <span class="flex-grow-1 fw-semibold" style="color:var(--azul_2);">
                                        {{ $prod['nombre'] ?? 'Producto' }}
                                    </span>
                                    <small class="text-muted me-2">Stock: <strong>{{ $inv->stock_actual ?? 0 }}</strong></small>
                                    <input type="number" name="garrafones[{{ $loop->index }}][cantidad]"
                                        min="0" value="0" placeholder="0"
                                        max="{{ $inv->stock_actual ?? 0 }}">
                                </div>
                                @endif
                                @endforeach
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Solo se descuentan los productos con cantidad mayor a 0. Si el stock es insuficiente se mostrará un error.
                            </small>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-primary-custom px-4">
                        <i class="fas fa-truck-loading me-1"></i> Habilitar Ruta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Rutas agrupadas por día
const rutasPorDia = @json($rutas->groupBy('dia_reparto'));

function abrirModalAsignacion() {
    document.getElementById('selectDiaModal').value = '';
    document.getElementById('selectRutaModal').innerHTML = '<option value="">— Primero selecciona un día —</option>';
    document.getElementById('inputTitulo').value = '';
    document.getElementById('inputDia').value = '';
    new bootstrap.Modal(document.getElementById('modalAsignacion')).show();
}

function cargarRutasDia(dia) {
    const sel = document.getElementById('selectRutaModal');
    sel.innerHTML = '<option value="">— Selecciona una ruta —</option>';
    document.getElementById('inputDia').value = dia;
    const rutas = rutasPorDia[dia] || [];
    if (rutas.length === 0) {
        sel.innerHTML = '<option value="">No hay rutas para este día</option>';
        return;
    }
    rutas.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r._id;
        opt.dataset.titulo = r.titulo || '';
        opt.textContent = r.titulo || r.repartidor_nombre || r._id;
        sel.appendChild(opt);
    });
}

function onRutaChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('inputTitulo').value = opt.dataset.titulo || '';
}

function filtrarDia(dia, btn) {
    document.querySelectorAll('.dia-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#tablaAsignaciones tr[data-dia]').forEach(tr => {
        tr.style.display = (dia === 'todos' || tr.dataset.dia === dia) ? '' : 'none';
    });
}

setTimeout(() => {
    document.querySelectorAll('.alert-custom').forEach(a => a.style.opacity = '0');
}, 3500);
</script>

@endsection
