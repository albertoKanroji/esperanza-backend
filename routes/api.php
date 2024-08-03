<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EntradaProductoController;
use App\Http\Controllers\EntradaRecogerProductoController;
use App\Http\Controllers\SalidaRecogerProductoController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('entrada-producto', [EntradaProductoController::class, 'store']);
Route::get('entradas-producto', [EntradaProductoController::class, 'index']);
Route::get('salidas-producto', [SalidaRecogerProductoController::class, 'index']);
Route::get('productos/cliente/{clientes_id}', [EntradaProductoController::class, 'getProductsByClient']);
Route::get('productos/cliente/{clientes_id}/deuda', [EntradaProductoController::class, 'getProductsByClientWithDebtSum']);
Route::get('productos/cliente/{clientes_id}/pagados', [EntradaProductoController::class, 'getProductsByClientWithSum']);
Route::post('entradas/recoger', [EntradaRecogerProductoController::class, 'store']);
Route::post('salida-producto', [SalidaRecogerProductoController::class, 'store']);
Route::get('/cliente/{id}/entradas-salidas', [EntradaRecogerProductoController::class, 'getEntradasYSaliadasPorCliente']);



Route::get('/prod/{id}', [EntradaRecogerProductoController::class, 'showProducto']);

Route::post('/salidas/{productos_id}/pagar-deuda', [EntradaRecogerProductoController::class, 'pagarDeuda']);
Route::get('productos/{id}/qr', [EntradaRecogerProductoController::class, 'generateQrCode']);
Route::get('productos/{id}/salida-qr', [SalidaRecogerProductoController::class, 'generateQrForSalida']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});