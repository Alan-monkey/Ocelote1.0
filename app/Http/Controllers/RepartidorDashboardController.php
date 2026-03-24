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

        return view('repartidor.ruta', compact('asignacion', 'ruta', 'user'));
    }

    public function marcarEntrega(Request $request)
    {
        $asignacionId = $request->input('asignacion_id');
        $clienteIndex = $request->input('cliente_index');

        $response = $this->pythonApi->marcarEntrega($asignacionId, $clienteIndex);

        return response()->json($response);
    }
}
