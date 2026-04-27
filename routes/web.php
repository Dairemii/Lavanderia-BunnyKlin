<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\BrickPagoController;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =========================================================
// 1. SECCIÓN PRINCIPAL
// =========================================================

Route::get('/', function () {
    return view('pages.pos', ['title' => 'Punto de Venta']);
})->name('pos');

Route::get('/historial', function () {
    return view('pages.historial', ['title' => 'Historial de Ventas']);
})->name('historial');

Route::get('/maquinas', function () {
    return view('pages.blank', ['title' => 'Máquinas IoT']); 
})->name('maquinas');

// =========================================================
// 2. SECCIÓN CATÁLOGOS
// =========================================================

Route::get('/servicios', function () {
    return view('pages.servicios', ['title' => 'Servicios de Lavado']);
})->name('servicios');

Route::get('/productos', function () {
    return view('pages.blank', ['title' => 'Productos Extra']);
})->name('productos');

// =========================================================
// 3. SECCIÓN OPERACIÓN
// =========================================================

Route::get('/insumos', function () {
    return view('pages.blank', ['title' => 'Inventario de Insumos']);
})->name('insumos');

Route::get('/clientes', function () {
    return view('pages.blank', ['title' => 'Gestión de Clientes']);
})->name('clientes');

Route::get('/caja', function () {
    return view('pages.blank', ['title' => 'Corte de Caja']);
})->name('caja');

// =========================================================
// RUTAS ORIGINALES DE LA PLANTILLA TAILADMIN
// =========================================================

Route::get('/calendar', function () { return view('pages.calender', ['title' => 'Calendar']); })->name('calendar');
Route::get('/profile', function () { return view('pages.profile', ['title' => 'Profile']); })->name('profile');
Route::get('/form-elements', function () { return view('pages.form.form-elements', ['title' => 'Form Elements']); })->name('form-elements');
Route::get('/basic-tables', function () { return view('pages.tables.basic-tables', ['title' => 'Basic Tables']); })->name('basic-tables');
Route::get('/blank', function () { return view('pages.blank', ['title' => 'Blank']); })->name('blank');
Route::get('/error-404', function () { return view('pages.errors.error-404', ['title' => 'Error 404']); })->name('error-404');

// ---------- AUTH ----------
Route::get('/signin', function () { return view('pages.auth.signin', ['title' => 'Sign In']); })->name('signin');
Route::get('/signup', function () { return view('pages.auth.signup', ['title' => 'Sign Up']); })->name('signup');

// =========================================================
// MERCADO PAGO E INTEGRACIONES
// =========================================================

// Checkout Pro
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