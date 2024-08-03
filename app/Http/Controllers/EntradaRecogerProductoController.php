<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EntradaRecogerProducto;
use App\Models\Producto;
use App\Models\EntradaProducto;
use Illuminate\Support\Facades\Validator;

class EntradaRecogerProductoController extends Controller
{
    public function index()
    {
        try {
            $entradas = EntradaRecogerProducto::with(['cliente', 'trabajador', 'producto', 'camion'])->get();
            return response()->json(['status' => 'success', 'data' => $entradas], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $entrada = EntradaRecogerProducto::with(['cliente', 'trabajador', 'producto', 'camion'])->findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $entrada], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clientes_id' => 'required|exists:clientes,id',
            'trabajadores_id' => 'required|exists:trabajadores,id',
            'productos_id' => 'required|exists:productos,id',
            'camiones_id' => 'required|exists:camiones,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        $producto = Producto::findOrFail($request->productos_id);

        // Verifica si el producto ya ha sido escaneado
        if ($producto->estado_escaneo === 'SI') {
            return response()->json([
                'status' => 'error',
                'data' => [
                    'message' => 'El producto ya ha sido escaneado.'
                ]
            ], 400);
        }

        try {
            // Verifica si el producto tiene deuda pendiente
            if ($producto->estado_deuda === 'NO') {
                $entradas = EntradaProducto::where('clientes_id', $request->clientes_id)
                    ->where('productos_id', $request->productos_id)
                    ->get();

                $totalDeuda = $entradas->sum('total_deuda');

                if ($totalDeuda > 0) {
                    return response()->json([
                        'status' => 'error',
                        'data' => [
                            'message' => 'El producto tiene deuda.',
                            'total_deuda' => $totalDeuda
                        ]
                    ], 400);
                }
            }

            // Marca el producto como escaneado
            $producto->estado_escaneo = 'SI';
            $producto->save();

            // Crea la nueva entrada
            $entrada = EntradaRecogerProducto::create($request->all());

            return response()->json(['status' => 'success', 'data' => $entrada], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'clientes_id' => 'required|exists:clientes,id',
            'trabajadores_id' => 'required|exists:trabajadores,id',
            'productos_id' => 'required|exists:productos,id',
            'camiones_id' => 'required|exists:camiones,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        try {
            $entrada = EntradaRecogerProducto::findOrFail($id);
            $entrada->update($request->all());
            return response()->json(['status' => 'success', 'data' => $entrada], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $entrada = EntradaRecogerProducto::findOrFail($id);
            $entrada->delete();
            return response()->json(['status' => 'success', 'data' => null], 204);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function pagarDeuda(Request $request, $productos_id)
    {
        $validator = Validator::make($request->all(), [
            'clientes_id' => 'required|exists:clientes,id',
            'monto_pago' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        try {
            $producto = Producto::findOrFail($productos_id);

            // Verifica si el producto ya ha sido pagado
            if ($producto->estado_deuda === 'SI') {
                return response()->json([
                    'status' => 'error',
                    'data' => ['message' => 'La deuda del producto ya ha sido pagada.']
                ], 400);
            }

            // Verifica si hay entradas de productos para el cliente
            $entradas = EntradaProducto::where('clientes_id', $request->clientes_id)
                ->where('productos_id', $productos_id)
                ->get();

            // Calcula la deuda total
            $totalDeuda = $entradas->sum('total_deuda');

            if ($request->monto_pago < $totalDeuda) {
                return response()->json([
                    'status' => 'error',
                    'data' => ['message' => 'El monto de pago no cubre la deuda total.', 'deuda_total' => $totalDeuda]
                ], 400);
            }

            // Si el monto de pago cubre la deuda, actualiza el estado del producto y la deuda
            $producto->estado_deuda = 'SI';
            $producto->save();

            // Establece la deuda total a 0 para las entradas del producto
            $entradas->each(function ($entrada) {
                $entrada->total_deuda = 0;
                $entrada->save();
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => 'Deuda pagada completamente y estado del producto actualizado.',
                    'producto' => $producto,
                    'entradas' => $entradas
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
}
