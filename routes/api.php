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
Route::get('productos/cliente/{clientes_id}', [EntradaProductoController::class, 'getProductsByClient']);
Route::get('productos/cliente/{clientes_id}/deuda', [EntradaProductoController::class, 'getProductsByClientWithDebtSum']);
Route::post('entradas/recoger', [EntradaRecogerProductoController::class, 'store']);
Route::post('/salidas', [SalidaRecogerProductoController::class, 'store']);
Route::post('/salidas/{productos_id}/pagar-deuda', [EntradaRecogerProductoController::class, 'pagarDeuda']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
