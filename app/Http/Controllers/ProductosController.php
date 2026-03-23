<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Inventario;
use App\Services\PythonApiService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductosController extends Controller
{
    protected $pythonApi;

    public function __construct(PythonApiService $pythonApi)
    {
        $this->middleware('auth:usuarios');
        $this->pythonApi = $pythonApi;
    }

    public function crear()
    {
        $user = auth()->guard('usuarios')->user();
        return view('productos.crear', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|max:2048',
            'stock_inicial' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
        ]);

        // Preparar datos para la API Python
        $imagePath = null;
        if ($request->hasFile('imagen')) {
            $imagePath = $request->imagen->store('productos', 'public');
        }

        $data = [
            'nombre' => $request->nombre,
            'precio' => (float)$request->precio,
            'descripcion' => $request->descripcion,
            'imagen' => $imagePath,
            'stock_inicial' => (int)$request->stock_inicial,
            'stock_minimo' => (int)$request->stock_minimo,
            'fecha_actualizacion' => now()->toIso8601String(),
        ];

        // Enviar ÚNICAMENTE a la API Python
        $response = $this->pythonApi->createProducto($data);

        if ($response['success']) {
            return redirect()->back()->with('success', 'Producto creado con éxito');
        } else {
            Log::error('Error al crear producto en API Python: ' . ($response['error'] ?? 'Unknown error'));
            return redirect()->back()->with('error', 'Error al crear producto: ' . ($response['error'] ?? 'Error desconocido'));
        }
    }

    public function leer()
    {
        $user = auth()->guard('usuarios')->user();
        
        // Obtener productos ÚNICAMENTE desde la API Python
        $response = $this->pythonApi->getProductos();

        if ($response['success']) {
            // Convertir los datos de la API a objetos stdClass para compatibilidad con la vista
            $productos = collect($response['data'])->map(function($item) {
                return (object) [
                    '_id' => $item['_id'] ?? null,
                    'nombre' => $item['nombre'] ?? '',
                    'precio' => $item['precio'] ?? 0,
                    'descripcion' => $item['descripcion'] ?? '',
                    'imagen' => $item['imagen'] ?? null,
                ];
            });
            
            return view('productos.leer', compact('productos', 'user'));
        } else {
            Log::error('Error al obtener productos de API Python: ' . ($response['error'] ?? 'Unknown error'));
            return back()->with('error', 'No se pudieron cargar los productos. Verifica que la API Python esté activa.');
        }
    }

    public function eliminar()
    {
        $user = auth()->guard('usuarios')->user();
        
        // Obtener productos ÚNICAMENTE desde la API Python
        $response = $this->pythonApi->getProductos();

        if ($response['success']) {
            // Convertir los datos de la API a objetos stdClass
            $productos = collect($response['data'])->map(function($item) {
                return (object) [
                    '_id' => $item['_id'] ?? null,
                    'nombre' => $item['nombre'] ?? '',
                    'precio' => $item['precio'] ?? 0,
                    'descripcion' => $item['descripcion'] ?? '',
                    'imagen' => $item['imagen'] ?? null,
                ];
            });
            
            return view('productos.eliminar', compact('productos', 'user'));
        } else {
            Log::error('Error al obtener productos de API Python');
            return back()->with('error', 'No se pudieron cargar los productos');
        }
    }

    public function inventario()
    {
        $user = auth()->guard('usuarios')->user();

        $response = $this->pythonApi->getInventario();
        if (!$response['success']) {
            return back()->with('error', 'No se pudo cargar el inventario.');
        }

        $statsResponse = $this->pythonApi->getInventarioEstadisticas();
        $stats = $statsResponse['success'] ? $statsResponse['data'] : [
            'total_stock' => 0, 'total_productos' => 0, 'productos_bajo_stock' => 0
        ];

        $productos_con_inventario = collect($response['data'])->map(function($item) {
            $producto = (object) [
                '_id'         => $item['producto']['_id'] ?? null,
                'nombre'      => $item['producto']['nombre'] ?? 'Sin nombre',
                'precio'      => $item['producto']['precio'] ?? 0,
                'descripcion' => $item['producto']['descripcion'] ?? '',
                'imagen'      => $item['producto']['imagen'] ?? null,
            ];
            $inventario = (object) [
                '_id'                => $item['_id'] ?? null,
                'stock_actual'       => $item['stock_actual'] ?? 0,
                'stock_minimo'       => $item['stock_minimo'] ?? 0,
                'fecha_actualizacion'=> $item['fecha_actualizacion'] ?? null,
                'bajo_stock'         => ($item['stock_actual'] ?? 0) <= ($item['stock_minimo'] ?? 0),
            ];
            $producto->inventario = $inventario;
            return $producto;
        })->toArray();

        $total_stock         = $stats['total_stock'];
        $productos_bajo_stock = $stats['productos_bajo_stock'];

        // Cargar insumos para el modal
        $insumosResponse = $this->pythonApi->getInsumos();
        $insumos = $insumosResponse['success'] ? $insumosResponse['data'] : [];

        return view('inventario.index', compact(
            'productos_con_inventario', 'user', 'total_stock', 'productos_bajo_stock', 'insumos'
        ));
    }


    public function actualizarStock(Request $request)
{
    $request->validate([
        'producto_id' => 'required',
        'nuevo_stock' => 'required|integer|min:0',
        'motivo'      => 'required|string',
        'insumos'     => 'required|array|min:1',
        'insumos.*'   => 'required|string',
    ]);

    $nuevoStock  = (int) $request->nuevo_stock;
    $insumoIds   = $request->insumos;

    // Verificar stock suficiente en cada insumo seleccionado
    // Primero obtener el stock actual del producto para calcular la diferencia
    $invActual = $this->pythonApi->verificarStock($request->producto_id);
    if (!$invActual['success']) {
        return back()->with('error', 'No se pudo verificar el inventario actual.');
    }
    $stockActual  = (int) ($invActual['data']['stock_actual'] ?? 0);
    $diferencia = $nuevoStock; // es la cantidad a agregar, no el valor final

    // Solo descontar insumos si se están agregando unidades
    if ($diferencia > 0) {
        // Verificar que todos los insumos tengan suficiente cantidad
        $insuficientes = [];
        foreach ($insumoIds as $insumoId) {
            $insumoResp = $this->pythonApi->getInsumo($insumoId);
            if (!$insumoResp['success']) {
                return back()->with('error', 'No se pudo verificar el insumo seleccionado.');
            }
            $insumo = $insumoResp['data'];
            if ((int)$insumo['cantidad'] < $diferencia) {
                $insuficientes[] = $insumo['nombre'] . ' (disponible: ' . $insumo['cantidad'] . ', requerido: ' . $diferencia . ')';
            }
        }

        if (!empty($insuficientes)) {
            return back()->with('error', 'Stock insuficiente en: ' . implode(', ', $insuficientes));
        }

        // Descontar de cada insumo
        foreach ($insumoIds as $insumoId) {
            $insumoResp = $this->pythonApi->getInsumo($insumoId);
            $insumo     = $insumoResp['data'];
            $nuevaCantidad = (int)$insumo['cantidad'] - $diferencia;
            $this->pythonApi->updateInsumo($insumoId, ['cantidad' => $nuevaCantidad]);
        }
    }

    // Actualizar stock del producto
    $response = $this->pythonApi->updateInventarioByProducto($request->producto_id, [
    'stock_actual' => $stockActual + $nuevoStock,
    'motivo'       => $request->motivo,
    ]);

    if ($response['success']) {
        return back()->with('success', 'Stock actualizado y ' . count($insumoIds) . ' insumo(s) descontados correctamente.');
    }

    return back()->with('error', 'Error al actualizar stock: ' . ($response['error'] ?? 'Error desconocido'));
}


    public function destroy(Request $request)
    {
        $id = $request->input('IdProducto');

        // Eliminar ÚNICAMENTE en la API Python (que maneja MongoDB Atlas)
        $response = $this->pythonApi->deleteProducto($id);

        if ($response['success']) {
            return redirect()->back()->with('success', 'Producto eliminado con éxito');
        } else {
            Log::error('Error al eliminar producto en API Python: ' . ($response['error'] ?? 'Unknown error'));
            return redirect()->back()->with('error', 'Error al eliminar producto: ' . ($response['error'] ?? 'Error desconocido'));
        }
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|max:2048',
        ]);

        // Preparar datos para la API Python
        $data = [
            'nombre' => $request->nombre,
            'precio' => (float)$request->precio,
            'descripcion' => $request->descripcion,
        ];

        if ($request->hasFile('imagen')) {
            $imagePath = $request->imagen->store('productos', 'public');
            $data['imagen'] = $imagePath;
        }

        // Actualizar ÚNICAMENTE en la API Python
        $response = $this->pythonApi->updateProducto($producto->_id, $data);

        if ($response['success']) {
            return redirect()->back()->with('success', 'Producto actualizado con éxito');
        } else {
            Log::error('Error al actualizar producto en API Python: ' . ($response['error'] ?? 'Unknown error'));
            return redirect()->back()->with('error', 'Error al actualizar producto: ' . ($response['error'] ?? 'Error desconocido'));
        }
    }
}