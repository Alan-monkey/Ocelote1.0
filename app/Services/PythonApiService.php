<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonApiService
{
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('python_api.base_url');
        $this->timeout = config('python_api.timeout');
    }

    public function getProductos()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/productos');
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al obtener productos', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getProductos: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getProducto($id)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . "/productos/{$id}");
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? null];
            }
            return ['success' => false, 'error' => 'Producto no encontrado', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getProducto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getProductoById($id)
    {
        return $this->getProducto($id);
    }

    public function createProducto($data)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/productos', $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? $response->json()];
            }
            return ['success' => false, 'error' => 'Error al crear producto', 'status' => $response->status(), 'message' => $response->json()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::createProducto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateProducto($id, $data)
    {
        try {
            $response = Http::timeout($this->timeout)->put($this->baseUrl . "/productos/{$id}", $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? $response->json()];
            }
            return ['success' => false, 'error' => 'Error al actualizar producto', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::updateProducto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteProducto($id)
    {
        try {
            $response = Http::timeout($this->timeout)->delete($this->baseUrl . "/productos/{$id}");
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
            return ['success' => false, 'error' => 'Error al eliminar producto', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::deleteProducto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getInventario()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/inventario');
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al obtener inventario', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getInventario: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getInventarioByProducto($productoId)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . "/inventario/producto/{$productoId}");
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? null];
            }
            return ['success' => false, 'error' => 'Inventario no encontrado', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getInventarioByProducto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateInventarioByProducto($productoId, $data)
    {
        try {
            $response = Http::timeout($this->timeout)->put($this->baseUrl . "/inventario/producto/{$productoId}", $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? $response->json()];
            }
            return ['success' => false, 'error' => 'Error al actualizar inventario', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::updateInventarioByProducto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getInventarioEstadisticas()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/inventario/resumen/estadisticas');
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al obtener estadísticas', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getInventarioEstadisticas: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verificarStock($productoId)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . "/inventario/producto/{$productoId}");
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? null];
            }
            return ['success' => false, 'error' => 'No se pudo obtener el stock', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('PythonApiService::verificarStock: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function actualizarStockMasivo($items)
    {
        $resultados = [];
        $errores    = [];
        foreach ($items as $item) {
            $productoId      = $item['id'];
            $cantidadVendida = $item['cantidad'];
            $stockResponse   = $this->verificarStock($productoId);
            if (!$stockResponse['success']) {
                $errores[] = "Error al verificar stock de producto {$productoId}";
                continue;
            }
            $inventario  = $stockResponse['data'];
            $nuevoStock  = $inventario['stock_actual'] - $cantidadVendida;
            $updateResponse = $this->updateInventarioByProducto($productoId, ['stock_actual' => $nuevoStock]);
            if ($updateResponse['success']) {
                $resultados[] = ['producto_id' => $productoId, 'stock_anterior' => $inventario['stock_actual'], 'stock_nuevo' => $nuevoStock, 'success' => true];
            } else {
                $errores[] = "Error al actualizar el stock de producto {$productoId}";
            }
        }
        return ['success' => empty($errores), 'resultados' => $resultados, 'errores' => $errores];
    }

    // ===== INSUMOS =====

    public function getInsumos()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/insumos');
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al obtener insumos'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getInsumos: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getInsumo($id)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . "/insumos/{$id}");
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? null];
            }
            return ['success' => false, 'error' => 'Insumo no encontrado'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getInsumo: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createInsumo($data)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/insumos', $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al crear insumo'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::createInsumo: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateInsumo($id, $data)
    {
        try {
            $response = Http::timeout($this->timeout)->put($this->baseUrl . "/insumos/{$id}", $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al actualizar insumo'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::updateInsumo: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteInsumo($id)
    {
        try {
            $response = Http::timeout($this->timeout)->delete($this->baseUrl . "/insumos/{$id}");
            if ($response->successful()) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Error al eliminar insumo'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::deleteInsumo: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== CLIENTES =====

    public function getClientes()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/clientes');
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al obtener clientes'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getClientes: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createCliente($data)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/clientes', $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al crear cliente'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::createCliente: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateCliente($id, $data)
    {
        try {
            $response = Http::timeout($this->timeout)->put($this->baseUrl . "/clientes/{$id}", $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al actualizar cliente'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::updateCliente: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteCliente($id)
    {
        try {
            $response = Http::timeout($this->timeout)->delete($this->baseUrl . "/clientes/{$id}");
            if ($response->successful()) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Error al eliminar cliente'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::deleteCliente: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== REPARTIDORES =====

    public function getRepartidores()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/usuarios/repartidores');
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al obtener repartidores'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getRepartidores: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createRepartidor($data)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/repartidores', $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al crear repartidor'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::createRepartidor: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateRepartidor($id, $data)
    {
        try {
            $response = Http::timeout($this->timeout)->put($this->baseUrl . "/repartidores/{$id}", $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al actualizar repartidor'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::updateRepartidor: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteRepartidor($id)
    {
        try {
            $response = Http::timeout($this->timeout)->delete($this->baseUrl . "/repartidores/{$id}");
            if ($response->successful()) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Error al eliminar repartidor'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::deleteRepartidor: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== RUTAS DE REPARTO =====

    public function getRutasReparto()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/rutas-reparto');
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al obtener rutas'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getRutasReparto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createRutaReparto($data)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/rutas-reparto', $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al crear ruta'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::createRutaReparto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateRutaReparto($id, $data)
    {
        try {
            $response = Http::timeout($this->timeout)->put($this->baseUrl . "/rutas-reparto/{$id}", $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al actualizar ruta'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::updateRutaReparto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteRutaReparto($id)
    {
        try {
            $response = Http::timeout($this->timeout)->delete($this->baseUrl . "/rutas-reparto/{$id}");
            if ($response->successful()) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Error al eliminar ruta'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::deleteRutaReparto: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== ASIGNACIONES DE RUTA =====

    public function getAsignacionesRuta()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/asignaciones-ruta');
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al obtener asignaciones'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getAsignacionesRuta: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createAsignacionRuta($data)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/asignaciones-ruta', $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            $body    = $response->json();
            $errores = $body['detail']['errores'] ?? [$body['detail'] ?? 'Error al crear asignación'];
            return ['success' => false, 'errores' => $errores];
        } catch (\Exception $e) {
            Log::error('PythonApiService::createAsignacionRuta: ' . $e->getMessage());
            return ['success' => false, 'errores' => [$e->getMessage()]];
        }
    }

    public function updateAsignacionRuta($id, $data)
    {
        try {
            $response = Http::timeout($this->timeout)->put($this->baseUrl . "/asignaciones-ruta/{$id}", $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al actualizar asignación'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::updateAsignacionRuta: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteAsignacionRuta($id)
    {
        try {
            $response = Http::timeout($this->timeout)->delete($this->baseUrl . "/asignaciones-ruta/{$id}");
            if ($response->successful()) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Error al eliminar asignación'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::deleteAsignacionRuta: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== REPARTIDOR - RUTAS ASIGNADAS =====

    public function getRutasRepartidor($repartidorId)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . "/repartidores/{$repartidorId}/rutas-asignadas");
            if ($response->successful()) {
                $data = $response->json();
                return ['success' => true, 'data' => $data['data'] ?? [], 'dia_actual' => $data['dia_actual'] ?? ''];
            }
            return ['success' => false, 'error' => 'Error al obtener rutas del repartidor'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::getRutasRepartidor: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function iniciarRuta($asignacionId)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . "/asignaciones-ruta/{$asignacionId}/iniciar");
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al iniciar ruta'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::iniciarRuta: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function marcarEntrega($asignacionId, $clienteIndex)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . "/asignaciones-ruta/{$asignacionId}/entregar/{$clienteIndex}");
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al marcar entrega'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::marcarEntrega: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== VENTAS =====

    public function createVenta($data)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/ventas', $data);
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data'] ?? []];
            }
            return ['success' => false, 'error' => 'Error al registrar venta'];
        } catch (\Exception $e) {
            Log::error('PythonApiService::createVenta: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
