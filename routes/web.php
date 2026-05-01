<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\BrickPagoController;
use App\Http\Controllers\CalendarController;
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