<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\BrickPagoController;
use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Http;
use App\Models\Service;
use App\Models\Supply;
use App\Models\Subscription;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\TerminalController;
// Import para Controlador del historial de ventas
use App\Http\Controllers\SalesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =========================================================
// 1. SECCIÓN PRINCIPAL
// =========================================================

Route::get('/', function () {
    $services = App\Models\Service::query()->where('is_active', true)->get();
    $supplies = App\Models\Supply::query()->where('is_active', true)->get();
    $subscriptions = App\Models\Subscription::query()->where('is_active', true)->get();

    return view('pages.pos', [
        'title'         => 'Punto de Venta',
        'services'      => $services,
        'supplies'      => $supplies,
        'subscriptions' => $subscriptions
    ]);
})->name('pos');

Route::post('/catalogo/guardar', [CatalogoController::class, 'store'])->name('catalogo.store');
Route::put('/catalogo/actualizar', [CatalogoController::class, 'update'])->name('catalogo.update');
// Ruta para eliminar registros del catalogo
Route::delete('/catalogo/eliminar', [CatalogoController::class, 'destroy'])->name('catalogo.destroy');

// Ruta para registrar en el historial de compras
Route::post('/ventas/checkout', [SalesController::class, 'store'])->name('ventas.checkout');
// Ruta para obtener el historial de compras
Route::get('/ventas/api-historial', [SalesController::class, 'apiHistorial']);
// Ruta para borrar múltiples ventas a la vez
Route::delete('/ventas/bulk', [App\Http\Controllers\SalesController::class, 'destroyBulk'])->name('ventas.bulkDestroy');
// Ruta para borrar una sola venta
Route::delete('/ventas/{id}', [App\Http\Controllers\SalesController::class, 'destroy'])->name('ventas.destroy');

Route::get('/historial', function () {
    return view('pages.historial', ['title' => 'Historial de Ventas']);
})->name('historial');

Route::get('/maquinas', function () {
    return view('pages.blank', ['title' => 'Máquinas IoT']);
})->name('maquinas');

// =========================================================
// 2. SECCIÓN CATÁLOGOS
// =========================================================

Route::get('/catalogo', function () {
    return view('pages.catalogo', ['title' => 'Servicios y Productos']);
})->name('catalogo');

// =========================================================
// 3. SECCIÓN OPERACIÓN
// =========================================================

Route::get('/pedidos', function () {
    return view('pages.pedidos', ['title' => 'Pedidos y Encargos']);
})->name('pedidos');

Route::get('/clientes', function () {
    return view('pages.clientes', ['title' => 'Clientes y Suscripciones']);
})->name('clientes');

Route::get('/insumos', function () {
    $insumos = App\Models\Supply::where('is_active', true)->get();
    return view('pages.inventario', [
        'title' => 'Inventario de Insumos',
        'insumosDb' => $insumos
    ]);
})->name('insumos');

Route::get('/facturacion', function () {
    return view('pages.facturacion', ['title' => 'Facturación SAT']);
})->name('facturacion');

Route::get('/caja', function () {
    return view('pages.blank', ['title' => 'Corte de Caja']);
})->name('caja');

Route::get('/newcalendar', [CalendarController::class, 'index'])->name('calendar.index');

// =========================================================
// RUTAS DE MERCADO PAGO Y TRADICIONALES
// =========================================================

Route::get('/calendar', function () { return view('pages.calender', ['title' => 'Calendar']); })->name('calendar');
Route::get('/profile', function () { return view('pages.profile', ['title' => 'Profile']); })->name('profile');
Route::get('/signin', function () { return view('pages.auth.signin', ['title' => 'Sign In']); })->name('signin');
Route::get('/signup', function () { return view('pages.auth.signup', ['title' => 'Sign Up']); })->name('signup');

Route::post('/pagar', [PagoController::class, 'iniciarPago'])->name('pago.iniciar');
Route::get('/pago/exito', [PagoController::class, 'pagoExitoso'])->name('pago.exito');
Route::get('/pago/fallo', [PagoController::class, 'pagoFallido'])->name('pago.fallo');
Route::get('/pago/pendiente', [PagoController::class, 'pagoPendiente'])->name('pago.pendiente');

Route::get('/pagar-con-tarjeta', [BrickPagoController::class, 'mostrarFormulario'])->name('brick.form');
Route::post('/procesar-pago', [BrickPagoController::class, 'procesarPago'])->name('brick.procesar');

// =========================================================
// RUTAS DE TERMINAL POINT (FÍSICA)
// =========================================================

// Iniciar cobro en la terminal
Route::post('/terminal/cobrar', [TerminalController::class, 'cobrarEnTerminal']);
// Verificar estado del cobro (Polling)
Route::get('/terminal/estado/{id}', [TerminalController::class, 'verificarEstado']);

Route::get('/mis-terminales', function () {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer APP_USR-3611189794742413-041809-56f3d298a20d175ac8db7489fd3eef13-409289088'
    ])->get('https://api.mercadopago.com/point/integration-api/devices');
    return $response->json();
});