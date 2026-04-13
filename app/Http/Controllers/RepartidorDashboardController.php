<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PythonApiService;

class RepartidorDashboardController extends Controller
{
    protected $pythonApi;

    public function __construct(PythonApiService $pythonApi)
    {
        $this->middleware('auth:usuarios');
        $this->pythonApi = $pythonApi;
    }

    public function index()
    {
        $user = auth()->guard('usuarios')->user();
        $repartidorId = $user->_id;

        $response = $this->pythonApi->getRutasRepartidor($repartidorId);

        $rutas = collect($response['success'] ? $response['data'] : [])->map(fn($r) => (object) $r);
        $diaActual = $response['dia_actual'] ?? '';

        return view('repartidor.dashboard', compact('rutas', 'diaActual', 'user'));
    }

    public function iniciarRuta(Request $request)
    {
        $asignacionId = $request->input('asignacion_id');
        $response = $this->pythonApi->iniciarRuta($asignacionId);

        return $response['success']
            ? redirect()->route('repartidor.ruta', ['id' => $asignacionId])->with('success', 'Ruta iniciada')
            : redirect()->back()->with('error', 'Error al iniciar la ruta');
    }

    public function verRuta($id)
    {
        $user = auth()->guard('usuarios')->user();

        // Obtener asignación
        $asignacionRes = $this->pythonApi->getAsignacionesRuta();
        $asignaciones = collect($asignacionRes['success'] ? $asignacionRes['data'] : [])->map(fn($a) => (object) $a);
        $asignacion = $asignaciones->firstWhere('_id', $id);

        if (!$asignacion) {
            return redirect()->route('repartidor.dashboard')->with('error', 'Asignación no encontrada');
        }

        // Obtener ruta completa
        $rutasRes = $this->pythonApi->getRutasReparto();
        $rutas = collect($rutasRes['success'] ? $rutasRes['data'] : [])->map(fn($r) => (object) $r);
        $ruta = $rutas->firstWhere('_id', $asignacion->ruta_id);

        // Obtener datos completos de clientes (para garrafones_estimados y precio_garrafon)
        $clientesRes = $this->pythonApi->getClientes();
        $clientesData = collect($clientesRes['success'] ? $clientesRes['data'] : [])
            ->keyBy('_id')
            ->map(fn($c) => (object) $c);

        // Leer ganancia acumulada desde sesión
        $gananciaTotal = session('ruta_' . $id . '_ganancia', session('ganancias_ruta_' . $id, 0));

        // Stock de garrafones persistido (o el original si no hay sesión aún)
        $garrafonesRaw = is_object($asignacion) ? (array)($asignacion->garrafones ?? []) : (array)($asignacion['garrafones'] ?? []);
        $stockKey = 'ruta_' . $id . '_stock_garrafones';
        $garrafonesStock = session($stockKey, collect($garrafonesRaw)->map(fn($g) => [
            'nombre'   => is_array($g) ? ($g['nombre'] ?? 'Producto') : 'Producto',
            'cantidad' => (int)(is_array($g) ? ($g['cantidad'] ?? 0) : 0),
        ])->toArray());

        // Obtener insumos para selector de garrafones vacíos
        $insumosRes  = $this->pythonApi->getInsumos();
        $insumosList = collect($insumosRes['success'] ? $insumosRes['data'] : [])->map(fn($i) => (object)$i);

        return view('repartidor.ruta', compact('asignacion', 'ruta', 'clientesData', 'gananciaTotal', 'user', 'garrafonesStock', 'insumosList'));
    }

    public function registrarVentaRuta(Request $request)
    {
        $request->validate([
            'asignacion_id'   => 'required|string',
            'cliente_index'   => 'required|integer',
            'cliente_id'      => 'required|string',
            'cliente_nombre'  => 'required|string',
            'cantidad'        => 'required|numeric|min:1',
            'precio'          => 'required|numeric|min:0',
            'garrafones'      => 'nullable|array',
            'garrafones_vacios' => 'nullable|array',  // [{id, nombre, cantidad}]
        ]);

        $response = $this->pythonApi->marcarEntrega(
            $request->asignacion_id,
            $request->cliente_index
        );

        if (!$response['success']) {
            return response()->json(['success' => false, 'error' => 'Error al marcar entrega']);
        }

        $total = $request->cantidad * $request->precio;
        $keyBase = 'ruta_' . $request->asignacion_id;

        // Acumular ganancia
        session([$keyBase . '_ganancia' => session($keyBase . '_ganancia', 0) + $total]);

        // Descontar garrafones del stock en sesión
        $stockKey = $keyBase . '_stock_garrafones';
        // Inicializar desde la asignación si no existe aún en sesión
        if (!session()->has($stockKey)) {
            $stockInicial = collect($request->garrafones ?? [])->map(fn($g) => [
                'nombre'   => $g['nombre'] ?? 'Producto',
                'cantidad' => (int)($g['cantidad'] ?? 0),
            ])->toArray();
            session([$stockKey => $stockInicial]);
        }
        $stock = session($stockKey);
        $restante = (int)$request->cantidad;
        foreach ($stock as &$g) {
            if ($restante <= 0) break;
            $descuento = min($g['cantidad'], $restante);
            $g['cantidad'] -= $descuento;
            $restante -= $descuento;
        }
        unset($g);
        session([$stockKey => $stock]);


        // Acumular clientes atendidos
        $clientes = session($keyBase . '_clientes', []);
        $clientes[] = ['nombre' => $request->cliente_nombre, 'total' => $total];
        session([$keyBase . '_clientes' => $clientes]);

        // Acumular productos vendidos (garrafones de la asignación prorrateados por cliente)
        $productos = session($keyBase . '_productos', []);
        foreach ($request->garrafones ?? [] as $g) {
            $nombre = $g['nombre'] ?? 'Producto';
            $cant   = (int)($g['cantidad'] ?? 0);
            $productos[$nombre] = ($productos[$nombre] ?? 0) + $cant;
        }
        session([$keyBase . '_productos' => $productos]);

        // Acumular garrafones vacíos recibidos en esta entrega
        $vacios = session($keyBase . '_garrafones_vacios', []);
        foreach ($request->garrafones_vacios ?? [] as $gv) {
            $gvId     = $gv['id']     ?? '';
            $gvNombre = $gv['nombre'] ?? 'Insumo';
            $gvCant   = (int)($gv['cantidad'] ?? 0);
            if ($gvCant <= 0 || !$gvId) continue;
            if (isset($vacios[$gvId])) {
                $vacios[$gvId]['cantidad'] += $gvCant;
            } else {
                $vacios[$gvId] = ['id' => $gvId, 'nombre' => $gvNombre, 'cantidad' => $gvCant];
            }
        }
        session([$keyBase . '_garrafones_vacios' => $vacios]);

        return response()->json([
            'success'        => true,
            'total'          => $total,
            'ganancia_total' => session($keyBase . '_ganancia'),
            'stock_garrafones'  => session($stockKey),
        ]);
    }

    public function terminarRuta(Request $request)
    {
        $request->validate(['asignacion_id' => 'required|string']);

        $id      = $request->asignacion_id;
        $keyBase = 'ruta_' . $id;
        $user    = auth()->guard('usuarios')->user();

        $ganancia       = session($keyBase . '_ganancia', 0);
        $clientes       = session($keyBase . '_clientes', []);
        $productos      = session($keyBase . '_productos', []);
        $garrafonesVacios = session($keyBase . '_garrafones_vacios', []);

        // Marcar asignación como finalizada
        $this->pythonApi->updateAsignacionRuta($id, ['estado' => 'finalizada']);

        // Registrar en colección ventas
        $productosArr = [];
        foreach ($productos as $nombre => $cantidad) {
            $productosArr[] = ['nombre' => $nombre, 'cantidad' => $cantidad];
        }

        $this->pythonApi->createVenta([
            'tipo'              => 'ruta_reparto',
            'asignacion_id'     => $id,
            'repartidor_id'     => $user->_id ?? '',
            'repartidor_nombre' => $user->nombre ?? $user->name ?? '',
            'total'             => $ganancia,
            'clientes_atendidos'=> count($clientes),
            'productos'         => $productosArr,
            'fecha'             => now()->toISOString(),
        ]);

        // Sumar garrafones vacíos al inventario de insumos
        foreach ($garrafonesVacios as $gv) {
            $insumoRes = $this->pythonApi->getInsumo($gv['id']);
            if ($insumoRes['success'] && isset($insumoRes['data'])) {
                $insumo = $insumoRes['data'];
                $nuevaCantidad = (int)($insumo['cantidad'] ?? 0) + (int)$gv['cantidad'];
                $this->pythonApi->updateInsumo($gv['id'], [
                    'nombre'          => $insumo['nombre']          ?? $gv['nombre'],
                    'descripcion'     => $insumo['descripcion']     ?? '',
                    'unidad_medida'   => $insumo['unidad_medida']   ?? 'Piezas',
                    'cantidad'        => $nuevaCantidad,
                    'cantidad_minima' => $insumo['cantidad_minima'] ?? 0,
                ]);
            }
        }

        // Limpiar sesión de esta ruta
        session()->forget([$keyBase . '_ganancia', $keyBase . '_clientes', $keyBase . '_productos', $keyBase . '_stock_garrafones', $keyBase . '_garrafones_vacios']);
        // Mantener clave legacy por compatibilidad
        session()->forget('ganancias_ruta_' . $id);

        return response()->json([
            'success'           => true,
            'ganancia'          => $ganancia,
            'clientes'          => $clientes,
            'productos'         => $productosArr,
            'garrafones_vacios' => array_values($garrafonesVacios),
        ]);
    }

    public function marcarEntrega(Request $request)
    {
        $asignacionId = $request->input('asignacion_id');
        $clienteIndex = $request->input('cliente_index');

        $response = $this->pythonApi->marcarEntrega($asignacionId, $clienteIndex);

        return response()->json($response);
    }
}
