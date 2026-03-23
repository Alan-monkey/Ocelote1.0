<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PythonApiService;

class ClientesController extends Controller
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
        $response = $this->pythonApi->getClientes();

        if (!$response['success']) {
            return back()->with('error', 'No se pudieron cargar los clientes.');
        }

        $clientes = collect($response['data'])->map(fn($c) => (object) $c);
        return view('clientes.index', compact('clientes', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'               => 'required|string|max:255',
            'direccion'            => 'required|string',
            'telefono'             => 'required|string|max:20',
            'garrafones_estimados' => 'required|integer|min:0',
            'precio_garrafon'      => 'required|numeric|min:0',
            'dia_entrega'          => 'required|string',
        ]);

        $data = $request->only('nombre', 'direccion', 'telefono', 'garrafones_estimados', 'precio_garrafon', 'dia_entrega');

        // Foto de domicilio en base64
        if ($request->hasFile('foto_domicilio')) {
            $data['foto_domicilio'] = base64_encode(file_get_contents($request->file('foto_domicilio')->getRealPath()));
            $data['foto_domicilio_mime'] = $request->file('foto_domicilio')->getMimeType();
        }

        $response = $this->pythonApi->createCliente($data);

        return $response['success']
            ? redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.')
            : redirect()->back()->with('error', 'Error al crear cliente: ' . ($response['error'] ?? ''));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'               => 'required|string|max:255',
            'direccion'            => 'required|string',
            'telefono'             => 'required|string|max:20',
            'garrafones_estimados' => 'required|integer|min:0',
            'precio_garrafon'      => 'required|numeric|min:0',
            'dia_entrega'          => 'required|string',
        ]);

        $data = $request->only('nombre', 'direccion', 'telefono', 'garrafones_estimados', 'precio_garrafon', 'dia_entrega');

        if ($request->hasFile('foto_domicilio')) {
            $data['foto_domicilio'] = base64_encode(file_get_contents($request->file('foto_domicilio')->getRealPath()));
            $data['foto_domicilio_mime'] = $request->file('foto_domicilio')->getMimeType();
        }

        $response = $this->pythonApi->updateCliente($id, $data);

        return $response['success']
            ? redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.')
            : redirect()->back()->with('error', 'Error al actualizar cliente.');
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $response = $this->pythonApi->deleteCliente($id);

        return $response['success']
            ? redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.')
            : redirect()->back()->with('error', 'Error al eliminar cliente.');
    }
}
