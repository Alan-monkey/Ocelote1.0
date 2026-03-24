<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PythonApiService;

class RutasRepartoController extends Controller
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

        $repartidoresRes = $this->pythonApi->getRepartidores();
        $clientesRes     = $this->pythonApi->getClientes();
        $rutasRes        = $this->pythonApi->getRutasReparto();

        $repartidores = collect($repartidoresRes['success'] ? $repartidoresRes['data'] : [])->map(fn($r) => (object) $r);
        $clientes     = collect($clientesRes['success']     ? $clientesRes['data']     : [])->map(fn($c) => (object) $c);
        $rutas        = collect($rutasRes['success']        ? $rutasRes['data']        : [])->map(fn($r) => (object) $r);

        return view('rutas.index', compact('repartidores', 'clientes', 'rutas', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo'            => 'required|string|max:100',
            'repartidor_id'     => 'required|string',
            'repartidor_nombre' => 'required|string',
            'dia_reparto'       => 'required|string',
            'clientes'          => 'required|array|min:1',
            'clientes.*.id'     => 'required|string',
            'clientes.*.nombre' => 'required|string',
            'clientes.*.orden'  => 'required|integer|min:1',
        ]);

        $data = [
            'titulo'            => $request->titulo,
            'repartidor_id'     => $request->repartidor_id,
            'repartidor_nombre' => $request->repartidor_nombre,
            'dia_reparto'       => $request->dia_reparto,
            'clientes'          => $request->clientes,
        ];

        $response = $this->pythonApi->createRutaReparto($data);

        return $response['success']
            ? redirect()->route('rutas.index')->with('success', 'Ruta creada correctamente.')
            : redirect()->back()->with('error', 'Error al crear la ruta: ' . ($response['error'] ?? ''));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo'            => 'required|string|max:100',
            'repartidor_id'     => 'required|string',
            'repartidor_nombre' => 'required|string',
            'dia_reparto'       => 'required|string',
            'clientes'          => 'required|array|min:1',
            'clientes.*.id'     => 'required|string',
            'clientes.*.nombre' => 'required|string',
            'clientes.*.orden'  => 'required|integer|min:1',
        ]);

        $data = $request->only('titulo', 'repartidor_id', 'repartidor_nombre', 'dia_reparto', 'clientes');
        $response = $this->pythonApi->updateRutaReparto($id, $data);

        return $response['success']
            ? redirect()->route('rutas.index')->with('success', 'Ruta actualizada correctamente.')
            : redirect()->back()->with('error', 'Error al actualizar la ruta.');
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $response = $this->pythonApi->deleteRutaReparto($id);

        return $response['success']
            ? redirect()->route('rutas.index')->with('success', 'Ruta eliminada correctamente.')
            : redirect()->back()->with('error', 'Error al eliminar la ruta.');
    }

    // CRUD de Repartidores
    public function storeRepartidor(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
        ]);

        $response = $this->pythonApi->createRepartidor($request->only('nombre', 'telefono'));

        return $response['success']
            ? redirect()->route('rutas.index')->with('success', 'Repartidor creado correctamente.')
            : redirect()->back()->with('error', 'Error al crear repartidor.');
    }

    public function updateRepartidor(Request $request, $id)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
        ]);

        $response = $this->pythonApi->updateRepartidor($id, $request->only('nombre', 'telefono'));

        return $response['success']
            ? redirect()->route('rutas.index')->with('success', 'Repartidor actualizado.')
            : redirect()->back()->with('error', 'Error al actualizar repartidor.');
    }

    public function destroyRepartidor(Request $request)
    {
        $id = $request->input('id');
        $response = $this->pythonApi->deleteRepartidor($id);

        return $response['success']
            ? redirect()->route('rutas.index')->with('success', 'Repartidor eliminado.')
            : redirect()->back()->with('error', 'Error al eliminar repartidor.');
    }


    // ===== ASIGNACIONES DE RUTA =====

public function asignaciones()
{
    $user            = auth()->guard('usuarios')->user();
    $rutasRes        = $this->pythonApi->getRutasReparto();
    $asignacionesRes = $this->pythonApi->getAsignacionesRuta();
    $inventarioRes   = $this->pythonApi->getInventario();

    $rutas        = collect($rutasRes['success']        ? $rutasRes['data']        : [])->map(fn($r) => (object) $r);
    $asignaciones = collect($asignacionesRes['success'] ? $asignacionesRes['data'] : [])->map(fn($a) => (object) $a);
    $inventario   = collect($inventarioRes['success']   ? $inventarioRes['data']   : [])->map(fn($i) => (object) $i);

    return view('rutas.asignaciones', compact('rutas', 'asignaciones', 'inventario', 'user'));
}

public function storeAsignacion(Request $request)
{
    $request->validate([
        'ruta_id' => 'required|string',
        'titulo'  => 'required|string',
        'dia'     => 'required|string',
    ]);

    // Filtrar solo garrafones con cantidad > 0
    $garrafones = collect($request->garrafones ?? [])
        ->filter(fn($g) => isset($g['cantidad']) && (int)$g['cantidad'] > 0)
        ->values()
        ->toArray();

    if (empty($garrafones)) {
        return redirect()->back()->with('error', 'Debes asignar al menos un garrafón con cantidad mayor a 0.');
    }

    $data = [
        'ruta_id'    => $request->ruta_id,
        'titulo'     => $request->titulo,
        'dia'        => $request->dia,
        'garrafones' => $garrafones,
    ];

    $response = $this->pythonApi->createAsignacionRuta($data);

    if ($response['success']) {
        return redirect()->route('rutas.asignaciones')->with('success', 'Ruta asignada y stock descontado correctamente.');
    }

    $errores = implode(' | ', $response['errores'] ?? ['Error desconocido']);
    return redirect()->back()->with('error', $errores);
}

public function finalizarAsignacion(Request $request)
{
    $id = $request->input('id');
    $response = $this->pythonApi->updateAsignacionRuta($id, ['estado' => 'finalizada']);

    return $response['success']
        ? redirect()->route('rutas.asignaciones')->with('success', 'Ruta finalizada.')
        : redirect()->back()->with('error', 'Error al finalizar la ruta.');
}

public function destroyAsignacion(Request $request)
{
    $id = $request->input('id');
    $response = $this->pythonApi->deleteAsignacionRuta($id);

    return $response['success']
        ? redirect()->route('rutas.asignaciones')->with('success', 'Asignación eliminada.')
        : redirect()->back()->with('error', 'Error al eliminar la asignación.');
}

}
