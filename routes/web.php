<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\BrickPagoController;
use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Http;
// Imports para ruta principal /
use App\Models\Service;
use App\Models\Supply;
use App\Models\Subscription;
// Import para Controlador del catalogo
use App\Http\Controllers\CatalogoController;

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
            'services'     => $services,
            'supplies'       => $supplies,
            'subscriptions' => $subscriptions
        ]);
})->name('pos');

// Ruta para guardar cosas del catalogo
Route::post('/catalogo/guardar', [CatalogoController::class, 'store'])->name('catalogo.store');
// Ruta para editar cosas del catalogo
Route::put('/catalogo/actualizar', [CatalogoController::class, 'update'])->name('catalogo.update');
// Ruta para eliminar registros del catalogo
Route::delete('/catalogo/eliminar', [CatalogoController::class, 'destroy'])->name('catalogo.destroy');

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

// 👇 AQUÍ ESTÁ LA RUTA DE PEDIDOS Y ENCARGOS QUE FALTABA 👇
Route::get('/pedidos', function () {
    return view('pages.pedidos', ['title' => 'Pedidos y Encargos']);
})->name('pedidos');

// 👇 AQUÍ ESTÁ LA RUTA DE CLIENTES Y SUSCRIPCIONES 👇
Route::get('/clientes', function () {
    return view('pages.clientes', ['title' => 'Clientes y Suscripciones']);
})->name('clientes');

Route::get('/insumos', function () {
    return view('pages.blank', ['title' => 'Inventario de Insumos']);
})->name('insumos');

Route::get('/caja', function () {
    return view('pages.blank', ['title' => 'Corte de Caja']);
})->name('caja');

Route::get('/newcalendar', [CalendarController::class, 'index'])->name('calendar.index');

// =========================================================
// RUTAS DE LA PLANTILLA Y MERCADO PAGO
// =========================================================
Route::get('/calendar', function () { return view('pages.calender', ['title' => 'Calendar']); })->name('calendar');
Route::get('/profile', function () { return view('pages.profile', ['title' => 'Profile']); })->name('profile');
Route::get('/signin', function () { return view('pages.auth.signin', ['title' => 'Sign In']); })->name('signin');
Route::get('/signup', function () { return view('pages.auth.signup', ['title' => 'Sign Up']); })->name('signup');
Route::post('/pagar', [PagoController::class, 'iniciarPago'])->name('pago.iniciar');

Route::get('/pago/exito', [PagoController::class, 'pagoExitoso'])->name('pago.exito');
Route::get('/pago/fallo', [PagoController::class, 'pagoFallido'])->name('pago.fallo');
Route::get('/pago/pendiente', [PagoController::class, 'pagoPendiente'])->name('pago.pendiente');

// Checkout Bricks
Route::get('/pagar-con-tarjeta', [BrickPagoController::class, 'mostrarFormulario'])->name('brick.form');
Route::post('/procesar-pago', [BrickPagoController::class, 'procesarPago'])->name('brick.procesar');

// Consultar terminales físicas de Mercado Pago Point
Route::get('/mis-terminales', function () {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer TU_ACCESS_TOKEN_DE_PRODUCCION'
    ])->get('https://api.mercadopago.com/point/integration-api/devices');

    return $response->json();
});
