<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PythonApiService;
use Illuminate\Support\Facades\Log;

class InsumosController extends Controller
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
        $response = $this->pythonApi->getInsumos();

        if (!$response['success']) {
            return back()->with('error', 'No se pudieron cargar los insumos.');
        }

        $insumos = collect($response['data'])->map(fn($i) => (object) $i);
        return view('insumos.index', compact('insumos', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'required|string',
            'unidad_medida'   => 'required|string',
            'cantidad'        => 'required|integer|min:0',
            'cantidad_minima' => 'required|integer|min:0',
        ]);

        $response = $this->pythonApi->createInsumo($request->only(
            'nombre', 'descripcion', 'unidad_medida', 'cantidad', 'cantidad_minima'
        ));

        return $response['success']
            ? redirect()->route('insumos.index')->with('success', 'Insumo creado correctamente.')
            : redirect()->back()->with('error', 'Error al crear insumo: ' . ($response['error'] ?? ''));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'required|string',
            'unidad_medida'   => 'required|string',
            'cantidad'        => 'required|integer|min:0',
            'cantidad_minima' => 'required|integer|min:0',
        ]);

        $response = $this->pythonApi->updateInsumo($id, $request->only(
            'nombre', 'descripcion', 'unidad_medida', 'cantidad', 'cantidad_minima'
        ));

        return $response['success']
            ? redirect()->route('insumos.index')->with('success', 'Insumo actualizado correctamente.')
            : redirect()->back()->with('error', 'Error al actualizar insumo.');
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $response = $this->pythonApi->deleteInsumo($id);

        return $response['success']
            ? redirect()->route('insumos.index')->with('success', 'Insumo eliminado correctamente.')
            : redirect()->back()->with('error', 'Error al eliminar insumo.');
    }
}
