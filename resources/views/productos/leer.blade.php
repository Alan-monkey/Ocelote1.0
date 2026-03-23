@extends('layouts.app')
@section('content')

<div class="productos-container">
    <div class="bg-elements">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <div class="container py-4">

        <!-- Header -->
        <div class="productos-header">
            <div class="header-icon"><i class="fas fa-box-open"></i></div>
            <div class="header-title">
                <h4>Lista de Productos</h4>
                <p>Catálogo completo de nuestra purificadora de agua</p>
            </div>
            <div class="header-deco ms-auto">
                <span>💧</span><span>📋</span><span>💧</span>
            </div>
        </div>

        <!-- Tabla -->
        <div class="productos-table-container">
            <table class="table productos-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> Nombre</th>
                        <th><i class="fas fa-dollar-sign"></i> Precio</th>
                        <th><i class="fas fa-align-left"></i> Descripción</th>
                        <th><i class="fas fa-image"></i> Imagen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $p)
                    <tr>
                        <td data-label="Nombre">
                            <div class="producto-nombre-cell">
                                <i class="fas fa-droplet"></i>
                                <strong>{{ $p->nombre }}</strong>
                            </div>
                        </td>
                        <td data-label="Precio">
                            <span class="precio-badge">${{ number_format($p->precio, 2) }}</span>
                        </td>
                        <td data-label="Descripción">
                            <div class="descripcion-cell">{{ $p->descripcion }}</div>
                        </td>
                        <td data-label="Imagen">
                            @if($p->imagen)
                                <div class="imagen-container">
                                    <img src="{{ asset('storage/' . $p->imagen) }}" alt="{{ $p->nombre }}" class="producto-imagen">
                                </div>
                            @else
                                <span class="sin-imagen-badge"><i class="fas fa-image"></i> Sin imagen</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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

.productos-container {
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

/* Header */
.productos-header {
    background: linear-gradient(135deg, var(--azul_2) 0%, var(--azul_1) 100%);
    color: var(--blanco);
    padding: 25px 30px;
    display: flex; align-items: center; gap: 20px;
    border-radius: 24px 24px 0 0;
    position: relative; overflow: hidden; z-index: 10;
}
.productos-header::after {
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

/* Contenedor tabla */
.productos-table-container {
    background: rgba(255,255,255,0.97);
    border-radius: 0 0 24px 24px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(19,45,70,0.12);
    position: relative; z-index: 10;
}

/* Tabla */
.productos-table { width:100%; border-collapse:separate; border-spacing:0 12px; }

.productos-table thead th {
    background: linear-gradient(145deg, #e8f4f8, #d0eaf0);
    color: var(--azul_2);
    font-weight: 700; padding: 16px; border: none;
    font-size: .88rem; text-transform: uppercase; letter-spacing: .5px;
}
.productos-table thead th:first-child { border-radius:12px 0 0 12px; }
.productos-table thead th:last-child  { border-radius:0 12px 12px 0; }
.productos-table thead th i { margin-right:8px; color:var(--azul_1); }

.productos-table tbody tr {
    background: white;
    box-shadow: 0 5px 15px rgba(19,45,70,0.05);
    transition: all .3s ease;
    animation: fadeInUp .5s ease forwards;
    opacity: 0;
}
.productos-table tbody tr:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 30px rgba(19,45,70,0.13);
}
.productos-table tbody td { padding:18px 16px; border:none; vertical-align:middle; }
.productos-table tbody td:first-child { border-radius:12px 0 0 12px; }
.productos-table tbody td:last-child  { border-radius:0 12px 12px 0; }

@keyframes fadeInUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.productos-table tbody tr:nth-child(1)  { animation-delay:.05s; }
.productos-table tbody tr:nth-child(2)  { animation-delay:.1s; }
.productos-table tbody tr:nth-child(3)  { animation-delay:.15s; }
.productos-table tbody tr:nth-child(4)  { animation-delay:.2s; }
.productos-table tbody tr:nth-child(5)  { animation-delay:.25s; }
.productos-table tbody tr:nth-child(6)  { animation-delay:.3s; }
.productos-table tbody tr:nth-child(7)  { animation-delay:.35s; }
.productos-table tbody tr:nth-child(8)  { animation-delay:.4s; }
.productos-table tbody tr:nth-child(9)  { animation-delay:.45s; }
.productos-table tbody tr:nth-child(10) { animation-delay:.5s; }

/* Celda nombre */
.producto-nombre-cell { display:flex; align-items:center; gap:12px; }
.producto-nombre-cell i { font-size:1.4rem; color:var(--azul_1); }
.producto-nombre-cell strong { color:var(--azul_2); font-size:1rem; }

/* Badge precio */
.precio-badge {
    background: linear-gradient(135deg, var(--azul_3), #7ecfd1);
    padding: 8px 16px; border-radius: 30px;
    color: var(--azul_2); font-weight: 700; font-size: 1rem;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(168,218,220,0.4);
}

/* Descripción */
.descripcion-cell {
    color: #3a5a70;
    line-height: 1.6;
    border-left: 4px solid var(--azul_3);
    padding-left: 15px;
    font-size: .93rem;
    max-width: 350px;
}

/* Imagen */
.imagen-container {
    width: 90px; height: 90px;
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 8px 20px rgba(19,45,70,0.15);
    border: 3px solid white;
    transition: all .3s ease;
}
.imagen-container:hover {
    transform: scale(1.5) translateX(20px);
    box-shadow: 0 15px 30px rgba(19,45,70,0.25);
    z-index: 100; position: relative;
}
.producto-imagen { width:100%; height:100%; object-fit:cover; }

.sin-imagen-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px;
    background: #e8f4f8;
    border-radius: 30px;
    color: var(--azul_1);
    font-style: italic; font-size: .9rem;
    border: 1px dashed var(--azul_3);
}

@media (max-width:768px) {
    .productos-header { flex-direction:column; text-align:center; padding:20px; border-radius:16px 16px 0 0; }
    .header-deco { margin:0 auto; }
    .productos-table thead { display:none; }
    .productos-table tbody tr { display:block; margin-bottom:20px; }
    .productos-table tbody td {
        display:block; text-align:right; padding:12px 15px;
        position:relative; border-bottom:1px solid #e8f4f8;
        border-radius:0 !important;
    }
    .productos-table tbody td:before {
        content:attr(data-label); position:absolute; left:15px;
        font-weight:700; color:var(--azul_2); text-transform:uppercase; font-size:.83rem;
    }
    .producto-nombre-cell { justify-content:flex-end; }
    .producto-nombre-cell i { order:2; }
    .imagen-container:hover { transform:scale(1.2); }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

@endsection
