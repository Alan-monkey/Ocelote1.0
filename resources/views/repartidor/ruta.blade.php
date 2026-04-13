@extends('layouts.app')
@section('content')

<style>
:root { --azul_1:#457b9d; --azul_2:#132d46; --azul_3:#a8dadc; --verde_azul:#07cdaf; }

.ruta-container { min-height:100vh; background:linear-gradient(145deg,#e8f4f8,#d0eaf0); padding:30px 0; font-family:'Poppins','Segoe UI',sans-serif; }
.header-ruta { background:linear-gradient(135deg,var(--azul_2),var(--azul_1)); color:white; border-radius:24px; padding:25px 30px; margin-bottom:24px; box-shadow:0 10px 30px rgba(19,45,70,.2); }
.camino-container { background:white; border-radius:24px; padding:40px 30px; box-shadow:0 8px 20px rgba(19,45,70,.08); margin-bottom:30px; }

/* ===== ROAD MAP ===== */
.roadmap-wrap { position:relative; width:100%; max-width:480px; margin:0 auto; padding:20px 0 40px; }

.pin-wrap {
    position:absolute;
    transform:translate(-50%,-100%);
    display:flex; flex-direction:column; align-items:center;
    pointer-events:all; cursor:pointer;
    transition:transform .2s, opacity .4s;
}
.pin-wrap:hover { transform:translate(-50%,-100%) scale(1.1); }
.pin-wrap.entregado { cursor:default; opacity:.55; }
.pin-wrap.entregado:hover { transform:translate(-50%,-100%); }

.pin-icon { width:52px; height:62px; filter:drop-shadow(0 4px 8px rgba(0,0,0,.25)); transition:filter .2s; }
.pin-wrap:hover .pin-icon { filter:drop-shadow(0 6px 12px rgba(0,0,0,.35)); }

.pin-label {
    background:white; border-radius:10px; padding:4px 10px;
    font-size:.7rem; font-weight:700; color:var(--azul_2);
    box-shadow:0 2px 8px rgba(0,0,0,.15); white-space:nowrap;
    max-width:110px; overflow:hidden; text-overflow:ellipsis;
    margin-top:4px; text-align:center;
}
.pin-wrap.entregado .pin-label { background:#e5e7eb; color:#6b7280; }

/* Camión animado */
.truck-pin {
    position: absolute;
    font-size: 2.2rem;
    filter: drop-shadow(0 3px 6px rgba(0,0,0,.25));
    pointer-events: none;
    z-index: 20;
    /* transition se aplica via JS para controlar duración */
}
@keyframes truckBounce {
    0%,100% { margin-top: 0; }
    50%      { margin-top: -6px; }
}
.truck-pin.idle { animation: truckBounce 1.4s ease-in-out infinite; }

.end-pin { position:absolute; transform:translate(-50%,-100%); font-size:2rem; filter:drop-shadow(0 3px 6px rgba(0,0,0,.2)); }

.progreso-bar { background:#e5e7eb; border-radius:20px; height:14px; overflow:hidden; margin-top:28px; }
.progreso-fill { background:linear-gradient(90deg,var(--verde_azul),var(--azul_1)); height:100%; transition:width .5s ease; border-radius:20px; }

/* ===== MODAL VENTA ===== */
.modal-venta-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.5); z-index:9999;
    align-items:center; justify-content:center; padding:16px;
}
.modal-venta-overlay.show { display:flex; }
.modal-venta-card {
    background:white; border-radius:24px; width:100%; max-width:420px;
    box-shadow:0 20px 60px rgba(0,0,0,.25); overflow:hidden;
    animation:slideUp .25s ease;
}
@keyframes slideUp { from{transform:translateY(30px);opacity:0;} to{transform:translateY(0);opacity:1;} }
.modal-venta-header {
    background:linear-gradient(135deg,var(--azul_2),var(--azul_1));
    color:white; padding:20px 24px;
    display:flex; align-items:center; justify-content:space-between;
}
.modal-venta-body { padding:24px; }
.mv-field label { font-size:.82rem; font-weight:700; color:var(--azul_2); margin-bottom:5px; display:block; }
.mv-field input {
    width:100%; border:2px solid #d0eaf0; border-radius:12px;
    padding:10px 14px; font-size:1rem; font-weight:600;
    transition:border .2s; outline:none;
}
.mv-field input:focus { border-color:var(--azul_1); }
.mv-total {
    background:linear-gradient(135deg,#f0fdf4,#dcfce7);
    border-radius:14px; padding:14px 18px;
    display:flex; align-items:center; justify-content:space-between;
    border:2px solid #bbf7d0;
}
.mv-total .label { font-size:.82rem; font-weight:700; color:#065f46; }
.mv-total .amount { font-size:1.6rem; font-weight:800; color:#059669; }
.btn-vender {
    background:linear-gradient(135deg,var(--verde_azul),var(--azul_1));
    color:white; border:none; border-radius:14px;
    padding:13px; font-size:1rem; font-weight:700;
    width:100%; cursor:pointer; transition:opacity .2s;
}
.btn-vender:hover { opacity:.9; }
.btn-cancelar-mv {
    background:none; border:2px solid #e5e7eb; border-radius:14px;
    padding:11px; font-size:.9rem; font-weight:600; color:#6b7280;
    width:100%; cursor:pointer; transition:all .2s;
}
.btn-cancelar-mv:hover { border-color:#9ca3af; color:#374151; }
</style>

<div class="ruta-container">
<div class="container">

    {{-- Header --}}
    <div class="header-ruta">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h3 class="mb-1 fw-bold"><i class="fas fa-route me-2"></i>{{ $ruta->titulo ?? 'Ruta' }}</h3>
                <p class="mb-0" style="opacity:.85;">{{ $ruta->dia_reparto ?? '' }} — {{ count((array)($ruta->clientes ?? [])) }} clientes</p>
            </div>
            <a href="{{ route('repartidor.dashboard') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- Recuadro garrafones + ganancias --}}
    @php
        $garrafones      = is_object($asignacion) ? (array)($asignacion->garrafones ?? []) : (array)($asignacion['garrafones'] ?? []);
        $totalGarrafones = array_sum(array_column($garrafones, 'cantidad'));
        $asignacionId    = is_object($asignacion) ? $asignacion->_id : $asignacion['_id'];
        // $gananciaTotal viene del controlador (sesión)
    @endphp

    <div style="background:white; border-radius:20px; padding:20px 28px; box-shadow:0 8px 20px rgba(19,45,70,.08); margin-bottom:24px;">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div style="background:linear-gradient(135deg,#0ea5e9,#0369a1); border-radius:14px; padding:14px 18px; color:white; text-align:center; min-width:90px;">
            @php $totalRestante = array_sum(array_column($garrafonesStock, 'cantidad')); @endphp
            <div id="totalGarrafonesDisplay" style="font-size:2rem; font-weight:800; line-height:1;">{{ $totalRestante }}</div>
                <div style="font-size:.7rem; opacity:.9; margin-top:2px;">garrafones</div>
            </div>
            <div class="flex-grow-1">
                <p class="mb-1 fw-bold" style="color:var(--azul_2); font-size:.9rem;">
                    <i class="fas fa-tint me-1" style="color:#0ea5e9;"></i>Garrafones asignados a esta ruta
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($garrafonesStock as $idx => $g)
                        <span id="chip-garrafon-{{ $idx }}"
                            style="background:{{ $g['cantidad'] == 0 ? '#fee2e2' : '#e0f2fe' }};
                                    color:{{ $g['cantidad'] == 0 ? '#dc2626' : '#0369a1' }};
                                    border-radius:20px; padding:4px 12px; font-size:.78rem; font-weight:600;">
                            {{ $g['nombre'] }}: <strong id="chip-cant-{{ $idx }}">{{ $g['cantidad'] }}</strong>
                        </span>
                    @endforeach

                </div>
            </div>
            <div style="background:linear-gradient(135deg,#059669,#047857); border-radius:14px; padding:14px 18px; color:white; text-align:center; min-width:110px;">
                <div style="font-size:.65rem; opacity:.85; margin-bottom:2px;">GANANCIAS</div>
                <div id="gananciaDisplay" style="font-size:1.5rem; font-weight:800; line-height:1;">
                    ${{ number_format($gananciaTotal, 2) }}
                </div>
                <div style="font-size:.65rem; opacity:.75; margin-top:2px;">en esta ruta</div>
            </div>
        </div>
    </div>

    {{-- Mapa de ruta --}}
    <div class="camino-container">
        <h5 class="fw-bold mb-4" style="color:var(--azul_2);">
            <i class="fas fa-map-marked-alt me-2"></i>Recorrido de Entrega
        </h5>

        @php
            $clientes      = (array)($ruta->clientes ?? []);
            $totalClientes = count($clientes);
            $entregasArr   = is_object($asignacion) ? (array)($asignacion->entregas ?? []) : (array)($asignacion['entregas'] ?? []);
            $entregados    = count($entregasArr);
            $progreso      = $totalClientes > 0 ? ($entregados / $totalClientes) * 100 : 0;
            $pinColors     = ['#4CAF50','#FF9800','#9C27B0','#2196F3','#F44336','#00BCD4','#FF5722','#8BC34A'];
            $svgW = 400; $segH = 110;
            $svgH = max(300, ($totalClientes + 1) * $segH);

            $roadPoints = [['x' => 80, 'y' => $svgH - 40]];
            for ($i = 0; $i < $totalClientes; $i++) {
                $roadPoints[] = ['x' => ($i % 2 === 0) ? 320 : 80, 'y' => $svgH - 40 - ($i + 1) * $segH];
            }
            $roadPoints[] = ['x' => 200, 'y' => 40];

            $pathD = "M {$roadPoints[0]['x']} {$roadPoints[0]['y']}";
            for ($i = 1; $i < count($roadPoints); $i++) {
                $prev = $roadPoints[$i-1]; $curr = $roadPoints[$i];
                $cy = ($prev['y'] + $curr['y']) / 2;
                $pathD .= " C {$prev['x']} {$cy}, {$curr['x']} {$cy}, {$curr['x']} {$curr['y']}";
            }

            // Posición inicial del camión: último entregado + 1, o punto 0
            $truckPointIndex = min($entregados, count($roadPoints) - 1);
            $truckInitLeft   = ($roadPoints[$truckPointIndex]['x'] / $svgW) * 100;
            $truckInitTop    = ($roadPoints[$truckPointIndex]['y'] / $svgH) * 100;
        @endphp

        {{-- Puntos del camino para JS --}}
        <script>
        const SVG_W = {{ $svgW }};
        const SVG_H = {{ $svgH }};
        const roadPoints = @json($roadPoints);

        // Stock de garrafones (mutable)
        let garrafonesStock = @json($garrafonesStock);
        let totalGarrafonesRestantes = garrafonesStock.reduce((s, g) => s + g.cantidad, 0);



        const clientesData = {
            @foreach($clientes as $index => $cliente)
            @php
                $cId  = is_array($cliente) ? ($cliente['id'] ?? ($cliente['_id'] ?? '')) : '';
                $cNom = is_array($cliente) ? ($cliente['nombre'] ?? 'Cliente') : $cliente;
                $cInfo = isset($clientesData[$cId]) ? $clientesData[$cId] : null;
                $garEst  = $cInfo ? ($cInfo->garrafones_estimados ?? 1) : 1;
                $precGar = $cInfo ? ($cInfo->precio_garrafon ?? 0) : 0;
            @endphp
            {{ $index }}: {
                id: "{{ $cId }}",
                nombre: "{{ addslashes($cNom) }}",
                garrafones_estimados: {{ $garEst }},
                precio_garrafon: {{ $precGar }}
            },
            @endforeach
        };

        let gananciaAcumulada = {{ $gananciaTotal }};
        let entregadosCount   = {{ $entregados }};
        const TOTAL_CLIENTES  = {{ $totalClientes }};
        const ASIGNACION_ID   = '{{ $asignacionId }}';
        const CSRF            = '{{ csrf_token() }}';
        // índice actual del camión en roadPoints (0 = inicio, 1 = primer cliente, etc.)
        let truckPointIndex   = {{ $truckPointIndex }};
        </script>

        <div class="roadmap-wrap" style="height:{{ $svgH + 60 }}px;" id="roadmapWrap">

            {{-- SVG carretera --}}
            <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}"
                 style="position:absolute;top:0;left:0;width:100%;height:100%;"
                 xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <filter id="shadow">
                        <feDropShadow dx="3" dy="3" stdDeviation="4" flood-opacity="0.2"/>
                    </filter>
                </defs>
                <path d="{{ $pathD }}" fill="none" stroke="#5a6472" stroke-width="36"
                      stroke-linecap="round" stroke-linejoin="round" filter="url(#shadow)"/>
                <path d="{{ $pathD }}" fill="none" stroke="#78909c" stroke-width="32"
                      stroke-linecap="round" stroke-linejoin="round"/>
                <path d="{{ $pathD }}" fill="none" stroke="white" stroke-width="2.5"
                      stroke-dasharray="14,10" stroke-linecap="round" opacity="0.7"/>
            </svg>

            {{-- Camión (posición inicial = último entregado) --}}
            <span class="truck-pin idle" id="truckPin"
                  style="left:{{ $truckInitLeft }}%;top:{{ $truckInitTop }}%;transform:translate(-50%,-50%);">
                🚚
            </span>

            {{-- Pins de clientes --}}
            @foreach($clientes as $index => $cliente)
                @php
                    $punto     = $roadPoints[$index + 1];
                    $leftPct   = ($punto['x'] / $svgW) * 100;
                    $topPct    = ($punto['y'] / $svgH) * 100;
                    $entregado = in_array($index, $entregasArr);
                    $cNombre   = is_array($cliente) ? ($cliente['nombre'] ?? "Cliente ".($index+1)) : $cliente;
                    $color     = $entregado ? '#9ca3af' : $pinColors[$index % count($pinColors)];
                @endphp
                <div class="pin-wrap {{ $entregado ? 'entregado' : '' }}"
                     style="left:{{ $leftPct }}%;top:{{ $topPct }}%;"
                     id="pin-{{ $index }}"
                     onclick="abrirVenta({{ $index }})">
                    <svg class="pin-icon" viewBox="0 0 52 62" xmlns="http://www.w3.org/2000/svg">
                        <path d="M26 2C15.5 2 7 10.5 7 21c0 14.5 19 39 19 39s19-24.5 19-39C45 10.5 36.5 2 26 2z"
                              fill="{{ $color }}" stroke="white" stroke-width="2.5"/>
                        <circle cx="26" cy="21" r="9" fill="white" opacity="0.3"/>
                        @if($entregado)
                            <text x="26" y="26" text-anchor="middle" fill="white" font-size="14" font-weight="bold">✓</text>
                        @else
                            <text x="26" y="26" text-anchor="middle" fill="white" font-size="14" font-weight="bold">{{ $index + 1 }}</text>
                        @endif
                    </svg>
                    <div class="pin-label">{{ Str::limit($cNombre, 14) }}</div>
                </div>
            @endforeach

            @php $lastPt = end($roadPoints); @endphp
            <span class="end-pin"
                  style="left:{{ ($lastPt['x'] / $svgW) * 100 }}%;top:{{ ($lastPt['y'] / $svgH) * 100 }}%;">
                🏁
            </span>
        </div>

        <div class="progreso-bar">
            <div class="progreso-fill" id="progresoFill" style="width:{{ $progreso }}%"></div>
        </div>
        <p class="text-center mt-2 mb-0 fw-bold" id="progresoTexto" style="color:var(--azul_2);">
            {{ $entregados }} / {{ $totalClientes }} entregas — {{ round($progreso) }}%
        </p>

        {{-- Botón terminar ruta --}}
        <div class="text-center mt-4" id="btnTerminarWrap"
             style="{{ $entregados >= $totalClientes && $totalClientes > 0 ? '' : 'display:none;' }}">
            <button onclick="terminarRuta()"
                    style="background:linear-gradient(135deg,#059669,#047857);color:white;border:none;
                           border-radius:16px;padding:14px 40px;font-size:1rem;font-weight:700;
                           cursor:pointer;box-shadow:0 6px 20px rgba(5,150,105,.35);transition:opacity .2s;">
                <i class="fas fa-flag-checkered me-2"></i>Terminar Ruta
            </button>
        </div>
    </div>

</div>
</div>

{{-- ===== MODAL RESUMEN FINAL ===== --}}
<div class="modal-venta-overlay" id="modalResumen">
    <div class="modal-venta-card" style="max-width:480px;">
        <div class="modal-venta-header" style="background:linear-gradient(135deg,#059669,#047857);">
            <div>
                <div style="font-size:.75rem;opacity:.8;margin-bottom:2px;">RUTA COMPLETADA</div>
                <div style="font-size:1.1rem;font-weight:700;">Resumen de la jornada</div>
            </div>
            <span style="font-size:1.8rem;">🏁</span>
        </div>
        <div class="modal-venta-body">

            {{-- Totales --}}
            <div class="d-flex gap-3 mb-4">
                <div style="flex:1;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-radius:14px;padding:16px;text-align:center;border:2px solid #bbf7d0;">
                    <div style="font-size:.7rem;font-weight:700;color:#065f46;margin-bottom:4px;">GANANCIAS</div>
                    <div id="res-ganancia" style="font-size:1.6rem;font-weight:800;color:#059669;">$0.00</div>
                </div>
                <div style="flex:1;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:14px;padding:16px;text-align:center;border:2px solid #bfdbfe;">
                    <div style="font-size:.7rem;font-weight:700;color:#1e40af;margin-bottom:4px;">CLIENTES</div>
                    <div id="res-clientes-count" style="font-size:1.6rem;font-weight:800;color:#2563eb;">0</div>
                </div>
            </div>

            {{-- Productos vendidos --}}
            <div style="margin-bottom:16px;">
                <p style="font-size:.82rem;font-weight:700;color:var(--azul_2);margin-bottom:8px;">
                    <i class="fas fa-tint me-1" style="color:#0ea5e9;"></i>Garrafones vendidos
                </p>
                <div id="res-productos" class="d-flex flex-wrap gap-2"></div>
            </div>

            {{-- Garrafones vacíos recibidos --}}
            <div style="margin-bottom:16px;" id="res-vacios-wrap">
                <p style="font-size:.82rem;font-weight:700;color:var(--azul_2);margin-bottom:8px;">
                    <i class="fas fa-recycle me-1" style="color:#0ea5e9;"></i>Garrafones vacíos recibidos
                </p>
                <div id="res-vacios" class="d-flex flex-wrap gap-2">
                    <span style="color:#9ca3af;font-size:.8rem;">Ninguno registrado</span>
                </div>
            </div>

            {{-- Clientes atendidos --}}
            <div style="margin-bottom:20px;">
                <p style="font-size:.82rem;font-weight:700;color:var(--azul_2);margin-bottom:8px;">
                    <i class="fas fa-users me-1"></i>Clientes atendidos
                </p>
                <div id="res-clientes-lista"
                     style="max-height:160px;overflow-y:auto;border:2px solid #e5e7eb;border-radius:12px;padding:8px;">
                </div>
            </div>

            <a href="{{ route('repartidor.dashboard') }}"
               style="display:block;background:linear-gradient(135deg,var(--verde_azul),var(--azul_1));
                      color:white;border:none;border-radius:14px;padding:13px;font-size:1rem;
                      font-weight:700;text-align:center;text-decoration:none;">
                <i class="fas fa-home me-2"></i>Volver al inicio
            </a>
        </div>
    </div>
</div>

{{-- ===== MODAL DE VENTA ===== --}}
<div class="modal-venta-overlay" id="modalVenta">
    <div class="modal-venta-card">
        <div class="modal-venta-header">
            <div>
                <div style="font-size:.75rem;opacity:.8;margin-bottom:2px;">REGISTRAR VENTA</div>
                <div id="mv-nombre" style="font-size:1.1rem;font-weight:700;"></div>
            </div>
            <button onclick="cerrarVenta()"
                    style="background:rgba(255,255,255,.2);border:none;border-radius:10px;padding:6px 10px;color:white;cursor:pointer;font-size:1.1rem;">✕</button>
        </div>
        <div class="modal-venta-body">
            <input type="hidden" id="mv-index">
            <input type="hidden" id="mv-cliente-id">

            <div class="d-flex gap-3 mb-3">
                <div class="mv-field flex-grow-1">
                    <label><i class="fas fa-tint me-1"></i>Garrafones</label>
                    <input type="number" id="mv-cantidad" min="1" placeholder="0" oninput="calcularTotal()">
                    <small id="mv-sugerencia" style="color:#6b7280;font-size:.72rem;margin-top:3px;display:block;"></small>
                </div>
                <div class="mv-field flex-grow-1">
                    <label><i class="fas fa-dollar-sign me-1"></i>Precio c/u</label>
                    <input type="number" id="mv-precio" min="0" step="0.01" placeholder="0.00" oninput="calcularTotal()">
                    <small id="mv-precio-sugerencia" style="color:#6b7280;font-size:.72rem;margin-top:3px;display:block;"></small>
                </div>
            </div>

            <div class="mv-total mb-4">
                <span class="label"><i class="fas fa-coins me-1"></i>Total de la venta</span>
                <span class="amount" id="mv-total">$0.00</span>
            </div>

            {{-- Garrafones vacíos (opcional) --}}
            <div style="border:2px dashed #d0eaf0;border-radius:14px;padding:14px;margin-bottom:16px;">
                <div class="d-flex align-items-center justify-content-between mb-2" style="cursor:pointer;" onclick="toggleVacios()">
                    <span style="font-size:.82rem;font-weight:700;color:var(--azul_2);">
                        <i class="fas fa-recycle me-1" style="color:#0ea5e9;"></i>Agregar garrafón vacío
                        <span style="font-size:.7rem;font-weight:400;color:#6b7280;">(opcional)</span>
                    </span>
                    <i class="fas fa-chevron-down" id="vaciosChevron" style="color:#94a3b8;transition:transform .2s;"></i>
                </div>
                <p style="font-size:.72rem;color:#f59e0b;font-weight:600;margin:0 0 10px;display:none;" id="vaciosAviso">
                    <i class="fas fa-exclamation-triangle me-1"></i>Solo seleccionar garrafones
                </p>
                <div id="vaciosPanel" style="display:none;">
                    <div id="vaciosLista">
                        @foreach($insumosList as $ins)
                        <div class="d-flex align-items-center gap-2 mb-2" id="vacio-row-{{ $ins->_id }}">
                            <label style="flex:1;font-size:.8rem;font-weight:600;color:var(--azul_2);cursor:pointer;display:flex;align-items:center;gap:8px;">
                                <input type="checkbox"
                                       id="vacio-chk-{{ $ins->_id }}"
                                       value="{{ $ins->_id }}"
                                       data-nombre="{{ addslashes($ins->nombre) }}"
                                       onchange="toggleVacioInput('{{ $ins->_id }}')"
                                       style="width:16px;height:16px;accent-color:var(--azul_1);">
                                {{ $ins->nombre }}
                            </label>
                            <input type="number"
                                   id="vacio-cant-{{ $ins->_id }}"
                                   min="1" placeholder="Cant."
                                   style="width:80px;border:2px solid #d0eaf0;border-radius:10px;padding:6px 10px;font-size:.85rem;font-weight:600;outline:none;display:none;"
                                   onfocus="this.style.borderColor='var(--azul_1)'"
                                   onblur="this.style.borderColor='#d0eaf0'">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <button class="btn-vender" onclick="confirmarVenta()">
                    <i class="fas fa-check-circle me-2"></i>Confirmar Venta
                </button>
                <button class="btn-cancelar-mv" onclick="cerrarVenta()">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
/* ---- Garrafones vacíos ---- */
function toggleVacios() {
    const panel   = document.getElementById('vaciosPanel');
    const aviso   = document.getElementById('vaciosAviso');
    const chevron = document.getElementById('vaciosChevron');
    const open    = panel.style.display === 'none';
    panel.style.display  = open ? 'block' : 'none';
    aviso.style.display  = open ? 'block' : 'none';
    chevron.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}

function toggleVacioInput(id) {
    const chk   = document.getElementById('vacio-chk-' + id);
    const input = document.getElementById('vacio-cant-' + id);
    input.style.display = chk.checked ? 'block' : 'none';
    if (chk.checked) { input.value = 1; input.focus(); }
    else input.value = '';
}

function resetVacios() {
    document.querySelectorAll('[id^="vacio-chk-"]').forEach(chk => {
        chk.checked = false;
        const id    = chk.value;
        const input = document.getElementById('vacio-cant-' + id);
        if (input) { input.style.display = 'none'; input.value = ''; }
    });
    document.getElementById('vaciosPanel').style.display  = 'none';
    document.getElementById('vaciosAviso').style.display  = 'none';
    document.getElementById('vaciosChevron').style.transform = 'rotate(0deg)';
}

function getVaciosSeleccionados() {
    const vacios = [];
    document.querySelectorAll('[id^="vacio-chk-"]:checked').forEach(chk => {
        const id     = chk.value;
        const nombre = chk.dataset.nombre;
        const cant   = parseInt(document.getElementById('vacio-cant-' + id)?.value) || 0;
        if (cant > 0) vacios.push({ id, nombre, cantidad: cant });
    });
    return vacios;
}

/* ---- Helpers ---- */
function pctToWrapPx(leftPct, topPct) {
    const wrap = document.getElementById('roadmapWrap');
    return {
        x: (leftPct / 100) * wrap.offsetWidth,
        y: (topPct  / 100) * wrap.offsetHeight
    };
}

/* ---- Mover camión ---- */
function moverCamion(toPointIndex, onDone) {
    const truck = document.getElementById('truckPin');
    const wrap  = document.getElementById('roadmapWrap');
    const pt    = roadPoints[toPointIndex];

    const leftPct = (pt.x / SVG_W) * 100;
    const topPct  = (pt.y / SVG_H) * 100;

    // Quitar bounce mientras viaja
    truck.classList.remove('idle');

    // Animar con CSS transition
    truck.style.transition = 'left 0.9s cubic-bezier(.4,0,.2,1), top 0.9s cubic-bezier(.4,0,.2,1)';
    truck.style.left = leftPct + '%';
    truck.style.top  = topPct  + '%';

    setTimeout(() => {
        truck.style.transition = '';
        truck.classList.add('idle');
        truckPointIndex = toPointIndex;
        if (onDone) onDone();
    }, 950);
}

/* ---- Modal ---- */
function abrirVenta(index) {
    const c   = clientesData[index];
    const pin = document.getElementById('pin-' + index);
    if (!c || (pin && pin.classList.contains('entregado'))) return;

    // Bloquear si no hay garrafones
    if (totalGarrafonesRestantes <= 0) {
        alert('No quedan garrafones disponibles. Termina la ruta.');
        return;
    }


    document.getElementById('mv-index').value      = index;
    document.getElementById('mv-cliente-id').value = c.id;
    document.getElementById('mv-nombre').textContent = c.nombre;

    document.getElementById('mv-cantidad').value = '';
    document.getElementById('mv-cantidad').placeholder = c.garrafones_estimados;
    document.getElementById('mv-sugerencia').textContent =
        c.garrafones_estimados ? 'Sugerido: ' + c.garrafones_estimados + ' garrafón(es)' : '';

    document.getElementById('mv-precio').value = '';
    document.getElementById('mv-precio').placeholder = parseFloat(c.precio_garrafon).toFixed(2);
    document.getElementById('mv-precio-sugerencia').textContent =
        c.precio_garrafon ? 'Precio habitual: $' + parseFloat(c.precio_garrafon).toFixed(2) : '';

    document.getElementById('mv-total').textContent = '$0.00';
    document.getElementById('modalVenta').classList.add('show');
}

function cerrarVenta() {
    document.getElementById('modalVenta').classList.remove('show');
    resetVacios();
}

function calcularTotal() {
    const idx      = document.getElementById('mv-index').value;
    const c        = clientesData[idx];
    const cantidad = parseFloat(document.getElementById('mv-cantidad').value) || 0;
    const precio   = parseFloat(document.getElementById('mv-precio').value)
                     || (c ? parseFloat(c.precio_garrafon) : 0);
    document.getElementById('mv-total').textContent = '$' + (cantidad * precio).toFixed(2);
}

function confirmarVenta() {
    const index     = parseInt(document.getElementById('mv-index').value);
    const clienteId = document.getElementById('mv-cliente-id').value;
    const c         = clientesData[index];

    const cantidad = parseFloat(document.getElementById('mv-cantidad').value)
                     || parseFloat(c.garrafones_estimados) || 1;
    const precio   = parseFloat(document.getElementById('mv-precio').value)
                     || parseFloat(c.precio_garrafon) || 0;

    if (cantidad <= 0) { alert('Ingresa la cantidad de garrafones.'); return; }

    const btnVender = document.querySelector('.btn-vender');
    btnVender.disabled = true;
    btnVender.textContent = 'Procesando...';

    // Garrafones de la asignación para registrar productos vendidos
    const garrafonesAsignacion = @json($garrafones);

    fetch('{{ route("repartidor.venta-ruta") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            asignacion_id:      ASIGNACION_ID,
            cliente_index:      index,
            cliente_id:         clienteId,
            cliente_nombre:     c.nombre,
            cantidad:           cantidad,
            precio:             precio,
            garrafones:         garrafonesAsignacion,
            garrafones_vacios:  getVaciosSeleccionados(),
        })
    })
    .then(r => r.json())
    .then(data => {
        btnVender.disabled = false;
        btnVender.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirmar Venta';

        if (!data.success) { alert(data.error || 'Error al registrar venta'); return; }

        cerrarVenta();

        // Pin gris
        const pin = document.getElementById('pin-' + index);
        if (pin) {
            pin.classList.add('entregado');
            const path = pin.querySelector('path');
            if (path) path.setAttribute('fill', '#9ca3af');
            const txt = pin.querySelector('text');
            if (txt) txt.textContent = '✓';
        }

        // Mover camión
        moverCamion(index + 1, null);

        // Ganancias
        gananciaAcumulada = data.ganancia_total;
        document.getElementById('gananciaDisplay').textContent =
            '$' + gananciaAcumulada.toLocaleString('es-MX', { minimumFractionDigits:2, maximumFractionDigits:2 });

        // Progreso
        entregadosCount++;
        const pct = Math.round((entregadosCount / TOTAL_CLIENTES) * 100);
        document.getElementById('progresoFill').style.width = pct + '%';
        document.getElementById('progresoTexto').textContent =
            entregadosCount + ' / ' + TOTAL_CLIENTES + ' entregas — ' + pct + '%';

        // Mostrar botón terminar si todos completados
        if (entregadosCount >= TOTAL_CLIENTES) {
            document.getElementById('btnTerminarWrap').style.display = '';
        }
        // Descontar garrafones vendidos
        const cantVendida = parseFloat(document.getElementById('mv-cantidad').value)
                            || parseFloat(clientesData[index]?.garrafones_estimados) || 1;
        if (data.stock_garrafones) {
            garrafonesStock = data.stock_garrafones;
            totalGarrafonesRestantes = garrafonesStock.reduce((s, g) => s + g.cantidad, 0);
            actualizarChipsGarrafones();
        }


    })
    .catch(() => {
        btnVender.disabled = false;
        btnVender.innerHTML = '<i class="fas fa-check-circle me-2"></i>Confirmar Venta';
        alert('Error de conexión');
    });
}

function actualizarChipsGarrafones() {
    document.getElementById('totalGarrafonesDisplay').textContent = totalGarrafonesRestantes;
    garrafonesStock.forEach((g, i) => {
        const chipCant = document.getElementById('chip-cant-' + i);
        const chip     = document.getElementById('chip-garrafon-' + i);
        if (chipCant) chipCant.textContent = g.cantidad;
        if (chip) {
            chip.style.background = g.cantidad === 0 ? '#fee2e2' : '#e0f2fe';
            chip.style.color      = g.cantidad === 0 ? '#dc2626' : '#0369a1';
        }
    });
    if (totalGarrafonesRestantes <= 0) {
        document.querySelectorAll('.pin-wrap:not(.entregado)').forEach(pin => {
            pin.style.opacity = '0.4';
            pin.style.cursor  = 'not-allowed';
            pin.onclick = () => alert('No quedan garrafones disponibles. Termina la ruta.');
        });
    }
}

function descontarGarrafones(cantidadTotal) {
    // Distribuye el descuento proporcionalmente entre los tipos de garrafón
    let restante = cantidadTotal;
    for (let i = 0; i < garrafonesStock.length && restante > 0; i++) {
        const descuento = Math.min(garrafonesStock[i].cantidad, restante);
        garrafonesStock[i].cantidad -= descuento;
        restante -= descuento;
        // Actualizar chip visual
        const chipCant = document.getElementById('chip-cant-' + i);
        if (chipCant) chipCant.textContent = garrafonesStock[i].cantidad;
        // Chip rojo si llegó a 0
        const chip = document.getElementById('chip-garrafon-' + i);
        if (chip && garrafonesStock[i].cantidad === 0) {
            chip.style.background = '#fee2e2';
            chip.style.color = '#dc2626';
        }
    }
    // Actualizar contador total
    totalGarrafonesRestantes = garrafonesStock.reduce((s, g) => s + g.cantidad, 0);
    document.getElementById('totalGarrafonesDisplay').textContent = totalGarrafonesRestantes;

    // Si no quedan garrafones, bloquear todos los pins pendientes
    if (totalGarrafonesRestantes <= 0) {
        document.querySelectorAll('.pin-wrap:not(.entregado)').forEach(pin => {
            pin.style.opacity = '0.4';
            pin.style.cursor  = 'not-allowed';
            pin.onclick = () => {
                alert('No quedan garrafones disponibles. Termina la ruta.');
            };
        });
    }
}

function terminarRuta() {
    if (!confirm('¿Confirmar que terminaste la ruta?')) return;

    fetch('{{ route("repartidor.terminar-ruta") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ asignacion_id: ASIGNACION_ID })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { alert('Error al terminar la ruta'); return; }

        // Llenar modal resumen
        document.getElementById('res-ganancia').textContent =
            '$' + parseFloat(data.ganancia).toLocaleString('es-MX', { minimumFractionDigits:2, maximumFractionDigits:2 });
        document.getElementById('res-clientes-count').textContent = data.clientes.length;

        // Productos
        const prodWrap = document.getElementById('res-productos');
        prodWrap.innerHTML = '';
        data.productos.forEach(p => {
            const span = document.createElement('span');
            span.style.cssText = 'background:#e0f2fe;color:#0369a1;border-radius:20px;padding:5px 14px;font-size:.8rem;font-weight:700;';
            span.textContent = p.nombre + ': ' + p.cantidad;
            prodWrap.appendChild(span);
        });

        // Garrafones vacíos
        const vaciosWrap = document.getElementById('res-vacios');
        vaciosWrap.innerHTML = '';
        if (data.garrafones_vacios && data.garrafones_vacios.length > 0) {
            data.garrafones_vacios.forEach(gv => {
                const span = document.createElement('span');
                span.style.cssText = 'background:#ecfdf5;color:#065f46;border-radius:20px;padding:5px 14px;font-size:.8rem;font-weight:700;border:1px solid #bbf7d0;';
                span.innerHTML = '<i class="fas fa-recycle me-1"></i>' + gv.nombre + ': ' + gv.cantidad;
                vaciosWrap.appendChild(span);
            });
        } else {
            vaciosWrap.innerHTML = '<span style="color:#9ca3af;font-size:.8rem;">Ninguno registrado</span>';
        }

        // Clientes
        const cliWrap = document.getElementById('res-clientes-lista');
        cliWrap.innerHTML = '';
        data.clientes.forEach((c, i) => {
            const div = document.createElement('div');
            div.style.cssText = 'display:flex;justify-content:space-between;padding:7px 10px;border-radius:8px;' +
                                 (i % 2 === 0 ? 'background:#f8fafc;' : '');
            div.innerHTML = '<span style="font-weight:600;font-size:.83rem;color:#132d46;">' + c.nombre + '</span>' +
                            '<span style="font-weight:700;font-size:.83rem;color:#059669;">$' +
                            parseFloat(c.total).toFixed(2) + '</span>';
            cliWrap.appendChild(div);
        });

        document.getElementById('modalResumen').classList.add('show');
    })
    .catch(() => alert('Error de conexión'));
}

// Cerrar modal al click fuera
document.getElementById('modalVenta').addEventListener('click', function(e) {
    if (e.target === this) cerrarVenta();
});
</script>

@endsection
