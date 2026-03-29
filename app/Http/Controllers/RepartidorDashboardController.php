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

        return view('repartidor.ruta', compact('asignacion', 'ruta', 'clientesData', 'gananciaTotal', 'user'));
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
            'garrafones'      => 'nullable|array',  // [{nombre, cantidad}] de la asignación
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

        return response()->json([
            'success'        => true,
            'total'          => $total,
            'ganancia_total' => session($keyBase . '_ganancia'),
        ]);
    }

    public function terminarRuta(Request $request)
    {
        $request->validate(['asignacion_id' => 'required|string']);

        $id      = $request->asignacion_id;
        $keyBase = 'ruta_' . $id;
        $user    = auth()->guard('usuarios')->user();

        $ganancia  = session($keyBase . '_ganancia', 0);
        $clientes  = session($keyBase . '_clientes', []);
        $productos = session($keyBase . '_productos', []);

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

        // Limpiar sesión de esta ruta
        session()->forget([$keyBase . '_ganancia', $keyBase . '_clientes', $keyBase . '_productos']);
        // Mantener clave legacy por compatibilidad
        session()->forget('ganancias_ruta_' . $id);

        return response()->json([
            'success'   => true,
            'ganancia'  => $ganancia,
            'clientes'  => $clientes,
            'productos' => $productosArr,
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
