@extends('layouts.app')
@section('content')

<div class="clientes-container">
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

        <div class="clientes-header">
            <div class="header-icon"><i class="fas fa-users"></i></div>
            <div class="header-title">
                <h4>Gestión de Clientes</h4>
                <p>Alta, baja y modificación de clientes</p>
            </div>
            <button class="btn-nuevo ms-auto" onclick="abrirModalCrear()">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </button>
        </div>

        <div class="clientes-card">
            <div class="table-responsive">
                <table class="table clientes-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Garrafones Est.</th>
                            <th>Precio Garrafón</th>
                            <th>Día Entrega</th>
                            <th>Foto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientes as $cliente)
                        <tr>
                            <td><strong>{{ $cliente->nombre }}</strong></td>
                            <td>{{ $cliente->direccion }}</td>
                            <td>{{ $cliente->telefono }}</td>
                            <td>{{ $cliente->garrafones_estimados }}</td>
                            <td>${{ number_format($cliente->precio_garrafon, 2) }}</td>
                            <td><span class="badge-dia">{{ $cliente->dia_entrega }}</span></td>
                            <td>
                                @if(!empty($cliente->foto_domicilio))
                                    <img src="data:{{ $cliente->foto_domicilio_mime ?? 'image/jpeg' }};base64,{{ $cliente->foto_domicilio }}"
                                         class="foto-thumb" onclick="verFoto(this.src)" title="Ver foto">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn-accion editar" onclick="abrirModalEditar(
                                    '{{ $cliente->_id }}',
                                    '{{ addslashes($cliente->nombre) }}',
                                    '{{ addslashes($cliente->direccion) }}',
                                    '{{ $cliente->telefono }}',
                                    {{ $cliente->garrafones_estimados }},
                                    {{ $cliente->precio_garrafon }},
                                    '{{ $cliente->dia_entrega }}'
                                )"><i class="fas fa-edit"></i></button>
                                <button class="btn-accion eliminar" onclick="abrirModalEliminar('{{ $cliente->_id }}', '{{ addslashes($cliente->nombre) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-users fa-3x mb-3" style="color:#457b9d;opacity:.4;"></i>
                                <p style="color:#457b9d;">No hay clientes registrados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CREAR --}}
<div class="modal-overlay" id="modalCrear">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="fas fa-user-plus"></i> Nuevo Cliente</h5>
            <button onclick="cerrarModal('modalCrear')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('clientes.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Garrafones Estimados</label>
                        <input type="number" name="garrafones_estimados" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Precio Garrafón ($)</label>
                        <input type="number" name="precio_garrafon" class="form-control" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Día de Entrega</label>
                    <select name="dia_entrega" class="form-control" required>
                        <option value="">Selecciona un día</option>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Foto de Domicilio</label>
                    <input type="file" name="foto_domicilio" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalCrear')">Cancelar</button>
                <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal-overlay" id="modalEditar">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="fas fa-edit"></i> Editar Cliente</h5>
            <button onclick="cerrarModal('modalEditar')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="formEditar" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" id="edit_direccion" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" id="edit_telefono" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Garrafones Estimados</label>
                        <input type="number" name="garrafones_estimados" id="edit_garrafones" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Precio Garrafón ($)</label>
                        <input type="number" name="precio_garrafon" id="edit_precio" class="form-control" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Día de Entrega</label>
                    <select name="dia_entrega" id="edit_dia" class="form-control" required>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Foto de Domicilio (dejar vacío para no cambiar)</label>
                    <input type="file" name="foto_domicilio" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL ELIMINAR --}}
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-box modal-sm">
        <div class="modal-header danger">
            <h5><i class="fas fa-trash"></i> Eliminar Cliente</h5>
            <button onclick="cerrarModal('modalEliminar')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body text-center">
            <i class="fas fa-exclamation-triangle fa-3x mb-3" style="color:#e63946;"></i>
            <p>¿Eliminar a <strong id="eliminar_nombre"></strong>?</p>
            <p class="text-muted small">Esta acción no se puede deshacer.</p>
        </div>
        <form method="POST" action="{{ route('clientes.destroy') }}">
            @csrf
            <input type="hidden" name="id" id="eliminar_id">
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEliminar')">Cancelar</button>
                <button type="submit" class="btn-eliminar"><i class="fas fa-trash"></i> Eliminar</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL FOTO --}}
<div class="modal-overlay" id="modalFoto" onclick="cerrarModal('modalFoto')">
    <div class="modal-box modal-foto">
        <img id="fotoGrande" src="" style="max-width:100%;border-radius:8px;">
    </div>
</div>

<style>
    :root {
        --azul_1: #457b9d;
        --azul_2: #132d46;
        --azul_3: #a8dadc;
        --blanco: #f1faee;
    }

    .clientes-container { min-height: 100vh; padding: 1rem; position: relative; }

    .bg-elements { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
    .bubble { position: absolute; border-radius: 50%; opacity: .06; background: var(--azul_1); }
    .b1 { width: 300px; height: 300px; top: -80px; right: -80px; }
    .b2 { width: 200px; height: 200px; bottom: 10%; left: -60px; }
    .b3 { width: 150px; height: 150px; bottom: 30%; right: 5%; }

    .container { position: relative; z-index: 1; }

    .alert-custom { padding: .9rem 1.2rem; border-radius: 10px; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; font-weight: 500; }
    .alert-custom.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
    .alert-custom.error   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }

    .clientes-header { display: flex; align-items: center; gap: 1rem; background: white; border-radius: 16px; padding: 1.2rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 20px rgba(19,45,70,.1); }
    .header-icon { width: 50px; height: 50px; background: linear-gradient(135deg, var(--azul_2), var(--azul_1)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.3rem; }
    .header-title h4 { margin: 0; color: var(--azul_2); font-weight: 700; }
    .header-title p  { margin: 0; color: var(--azul_1); font-size: .85rem; }

    .btn-nuevo { background: linear-gradient(135deg, var(--azul_2), var(--azul_1)); color: white; border: none; border-radius: 10px; padding: .6rem 1.2rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: .3s; }
    .btn-nuevo:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(19,45,70,.3); }

    .clientes-card { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(19,45,70,.08); }

    .clientes-table thead th { background: linear-gradient(135deg, var(--azul_2), var(--azul_1)); color: white; padding: .9rem 1rem; font-weight: 600; font-size: .85rem; border: none; }
    .clientes-table thead th:first-child { border-radius: 10px 0 0 0; }
    .clientes-table thead th:last-child  { border-radius: 0 10px 0 0; }
    .clientes-table tbody tr { transition: .2s; }
    .clientes-table tbody tr:hover { background: #f0f9ff; }
    .clientes-table td { padding: .8rem 1rem; vertical-align: middle; border-bottom: 1px solid #f0f0f0; font-size: .9rem; }

    .badge-dia { background: var(--azul_3); color: var(--azul_2); padding: .3rem .7rem; border-radius: 20px; font-size: .8rem; font-weight: 600; }

    .foto-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid var(--azul_3); transition: .2s; }
    .foto-thumb:hover { transform: scale(1.1); }

    .btn-accion { width: 34px; height: 34px; border: none; border-radius: 8px; cursor: pointer; transition: .2s; margin: 0 2px; }
    .btn-accion.editar   { background: #e0f2fe; color: #0369a1; }
    .btn-accion.eliminar { background: #fee2e2; color: #dc2626; }
    .btn-accion:hover { transform: translateY(-2px); }

    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 9999; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex; }
    .modal-box { background: white; border-radius: 16px; width: 100%; max-width: 540px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.3); animation: slideUp .3s ease; }
    .modal-box.modal-sm  { max-width: 400px; }
    .modal-box.modal-foto { max-width: 700px; background: transparent; box-shadow: none; }
    @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.2rem 1.5rem; border-bottom: 1px solid #f0f0f0; }
    .modal-header h5 { margin: 0; color: var(--azul_2); font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .modal-header.danger h5 { color: #dc2626; }
    .modal-header button { background: none; border: none; font-size: 1.1rem; color: #999; cursor: pointer; }

    .modal-body { padding: 1.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; font-size: .85rem; font-weight: 600; color: var(--azul_2); margin-bottom: .4rem; }
    .form-group .form-control { width: 100%; padding: .6rem .9rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: .9rem; transition: .2s; }
    .form-group .form-control:focus { outline: none; border-color: var(--azul_1); box-shadow: 0 0 0 3px rgba(69,123,157,.1); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    .modal-footer { display: flex; justify-content: flex-end; gap: .8rem; padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; }
    .btn-cancelar { background: #f3f4f6; color: #374151; border: none; border-radius: 8px; padding: .6rem 1.2rem; cursor: pointer; font-weight: 600; }
    .btn-guardar  { background: linear-gradient(135deg, var(--azul_2), var(--azul_1)); color: white; border: none; border-radius: 8px; padding: .6rem 1.2rem; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .btn-eliminar { background: linear-gradient(135deg, #dc2626, #ef4444); color: white; border: none; border-radius: 8px; padding: .6rem 1.2rem; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 6px; }
</style>

<script>
    function abrirModalCrear() {
        document.getElementById('modalCrear').classList.add('active');
    }

    function abrirModalEditar(id, nombre, direccion, telefono, garrafones, precio, dia) {
        document.getElementById('formEditar').action = '/clientes/' + id;
        document.getElementById('edit_nombre').value    = nombre;
        document.getElementById('edit_direccion').value = direccion;
        document.getElementById('edit_telefono').value  = telefono;
        document.getElementById('edit_garrafones').value = garrafones;
        document.getElementById('edit_precio').value    = precio;
        document.getElementById('edit_dia').value       = dia;
        document.getElementById('modalEditar').classList.add('active');
    }

    function abrirModalEliminar(id, nombre) {
        document.getElementById('eliminar_id').value    = id;
        document.getElementById('eliminar_nombre').textContent = nombre;
        document.getElementById('modalEliminar').classList.add('active');
    }

    function cerrarModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function verFoto(src) {
        document.getElementById('fotoGrande').src = src;
        document.getElementById('modalFoto').classList.add('active');
    }

    // Cerrar modales con Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active')
                    .forEach(m => m.classList.remove('active'));
        }
    });

    // Auto-cerrar alertas
    setTimeout(() => {
        document.querySelectorAll('.alert-custom').forEach(a => a.style.display = 'none');
    }, 4000);
</script>

@endsection
