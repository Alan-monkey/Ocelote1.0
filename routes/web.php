<?php

use App\Http\Controllers\LibrosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RegistrarseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\InsumosController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\RutasRepartoController;


Route::get('/', function () {
    return view('auth.login');
});

// Rutas públicas (sin autenticación)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Rutas para recuperación de contraseña
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.update');
Route::get('/password/show-link/{token}', [PasswordResetController::class, 'showGeneratedLink'])->name('password.show.link');

// Rutas para invitados (tipo 1)
Route::middleware(['auth:usuarios', 'check.user.type:1'])->group(function () {
    Route::get('/inicio', [LibrosController::class, 'inicioInv'])->name('inicio.invitado');

    



});

// Rutas compartidas de carrito (para ambos tipos de usuarios)
Route::middleware(['auth:usuarios'])->group(function () {
    Route::get('/carrito', [CarritoController::class, 'ver'])->name('carrito.ver');
    Route::post('/carrito/agregar/{id}', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::post('/carrito/actualizar/{id}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::get('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    Route::get('/carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
    Route::get('/pago', [CarritoController::class, 'mostrarPago'])->name('carrito.mostrar-pago');
    Route::post('/carrito/procesar-pago', [CarritoController::class, 'procesarPago'])->name('carrito.procesar-pago');
    Route::get('/pago-exito', [CarritoController::class, 'pagoExito'])->name('carrito.pago-exito');
    Route::get('/descargar-ticket', [CarritoController::class, 'descargarTicket'])->name('carrito.descargar-ticket');
    Route::get('/ventas/ticket/{id}', [CarritoController::class, 'ticket'])->name('ventas.ticket');
    // Dentro del grupo middleware(['auth:usuarios', 'check.user.type:0'])
Route::get('/clientes', [ClientesController::class, 'index'])->name('clientes.index');
Route::post('/clientes', [ClientesController::class, 'store'])->name('clientes.store');
Route::put('/clientes/{id}', [ClientesController::class, 'update'])->name('clientes.update');
Route::post('/clientes/destroy', [ClientesController::class, 'destroy'])->name('clientes.destroy');

});

// Rutas para empleados (tipo 0)
Route::middleware(['auth:usuarios', 'check.user.type:0'])->group(function () {
    // Rutas de libros para empleados
    Route::get('/libros/crear', [LibrosController::class, 'crear'])->name('libros.crear');
    Route::post('/libros/store', [LibrosController::class, 'store'])->name('libros.store');
    Route::get('/libros/leer', [LibrosController::class, 'leer'])->name('libros.leer');
    Route::put('/libros/{libro}', [LibrosController::class, 'update'])->name('libros.update');
    Route::get('/libros/eliminar', [LibrosController::class, 'eliminar'])->name('libros.eliminar');
    Route::post('/libros/destroy', [LibrosController::class, 'destroy'])->name('libros.destroy');
    Route::get('/libros/inicio', [LibrosController::class, 'inicio'])->name('libros.inicio');
    Route::get('/libros/consultar', [LibrosController::class, 'consultar'])->name('libros.consultar');
    
    // Rutas de productos para empleados
    Route::get('/productos/crear', [ProductosController::class, 'crear'])->name('productos.crear');
    Route::get('/productos/leer', [ProductosController::class, 'leer'])->name('productos.leer');
    Route::post('/productos/store', [ProductosController::class, 'store'])->name('productos.store');
    Route::put('/productos/{producto}', [ProductosController::class, 'update'])->name('productos.update');
    Route::get('/productos/eliminar', [ProductosController::class, 'eliminar'])->name('productos.eliminar');
    Route::post('/productos/destroy', [ProductosController::class, 'destroy'])->name('productos.destroy');
    Route::get('/inventario', [ProductosController::class, 'inventario'])->name('inventario.index');
    Route::post('/inventario/actualizar', [ProductosController::class, 'actualizarStock'])->name('inventario.actualizar');
    
    // Rutas para backups (solo empleados)
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::get('/backups/create', [BackupController::class, 'createBackupForm'])->name('backups.create');
    Route::post('/backups', [BackupController::class, 'createBackup'])->name('backups.store');
    Route::get('/backups/download/{filename}', [BackupController::class, 'downloadBackup'])->name('backups.download');
    Route::delete('/backups/{filename}', [BackupController::class, 'deleteBackup'])->name('backups.delete');
    Route::get('/backups/restore', [BackupController::class, 'restoreBackupForm'])->name('backups.restore.form');
    Route::get('/backups/restore/{filename}', [BackupController::class, 'restoreBackupForm'])->name('backups.restore.form.withfile');
    Route::post('/backups/restore', [BackupController::class, 'restoreBackup'])->name('backups.restore');
    Route::get('/backups/test-restore/{filename}', [BackupController::class, 'testRestore'])->name('backups.test');
    Route::get('/backups/debug/{filename}', [BackupController::class, 'debugBackup'])->name('backups.debug');
    Route::get('/backups/verify', [BackupController::class, 'verifyRestore'])->name('backups.verify');
    Route::post('/backups/verify-password', [BackupController::class, 'verifyPassword'])->name('backups.verify-password');
    Route::post('/backups/verify-password-restore', [BackupController::class, 'verifyPasswordRestore'])->name('backups.verify-password-restore');
    Route::post('/backups/verify-password-download', [BackupController::class, 'verifyPasswordDownload'])->name('backups.verify-password-download');
    Route::post('/backups/verify-password-delete', [BackupController::class, 'verifyPasswordDelete'])->name('backups.verify-password-delete');
    
    // Rutas de ventas (solo empleados)
    Route::get('/ventas', [App\Http\Controllers\VentasController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/reportes', [App\Http\Controllers\VentasController::class, 'reportes'])->name('ventas.reportes');
    Route::get('/ventas/mesas/disponibles', [App\Http\Controllers\VentasController::class, 'mesasDisponibles'])->name('ventas.mesas.disponibles');
    Route::get('/ventas/{id}', [App\Http\Controllers\VentasController::class, 'show'])->name('ventas.show');
    
    // Rutas de registro (solo empleados)
    Route::get('/libros/registrarse', [RegistrarseController::class, 'registrarse'])->name('libros.registrarse');
    Route::post('/libros/registrarse', [RegistrarseController::class, 'registrar'])->name('libros.registrar');

    // Rutas de insumos
    Route::get('/insumos', [InsumosController::class, 'index'])->name('insumos.index');
    Route::post('/insumos/store', [InsumosController::class, 'store'])->name('insumos.store');
    Route::put('/insumos/{id}', [InsumosController::class, 'update'])->name('insumos.update');
    Route::post('/insumos/destroy', [InsumosController::class, 'destroy'])->name('insumos.destroy');




    // Rutas de reparto (rutas estáticas ANTES de las dinámicas con {id})
    Route::get('/rutas-reparto', [RutasRepartoController::class, 'index'])->name('rutas.index');
    Route::post('/rutas-reparto', [RutasRepartoController::class, 'store'])->name('rutas.store');
    Route::post('/rutas-reparto/destroy', [RutasRepartoController::class, 'destroy'])->name('rutas.destroy');

    // Asignaciones de ruta (antes de la ruta dinámica {id})
    Route::get('/rutas-reparto/asignaciones', [RutasRepartoController::class, 'asignaciones'])->name('rutas.asignaciones');
    Route::post('/rutas-reparto/asignaciones', [RutasRepartoController::class, 'storeAsignacion'])->name('rutas.asignaciones.store');
    Route::post('/rutas-reparto/asignaciones/finalizar', [RutasRepartoController::class, 'finalizarAsignacion'])->name('rutas.asignaciones.finalizar');
    Route::post('/rutas-reparto/asignaciones/destroy', [RutasRepartoController::class, 'destroyAsignacion'])->name('rutas.asignaciones.destroy');

    // Ruta dinámica al final para no capturar las rutas estáticas
    Route::put('/rutas-reparto/{id}', [RutasRepartoController::class, 'update'])->name('rutas.update');


});