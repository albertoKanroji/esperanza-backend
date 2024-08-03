<?php

namespace App\Http\Controllers;

use App\Models\SalidaRecogerProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\EntradaProducto;
use App\Models\Producto;

class SalidaRecogerProductoController extends Controller
{
    public function index()
    {
        try {
            $salidas = SalidaRecogerProducto::with(['trabajador', 'producto', 'cliente', 'camion'])->get();
            return response()->json(['status' => 'success', 'data' => $salidas], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $salida = SalidaRecogerProducto::with(['trabajador', 'producto', 'cliente', 'camion'])->findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $salida], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trabajadores_id' => 'required|exists:trabajadores,id',
            'productos_id' => 'required|exists:productos,id',
            'clientes_id' => 'required|exists:clientes,id',
            'camiones_id' => 'required|exists:camiones,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        try {
            // Encuentra el producto
            $producto = Producto::findOrFail($request->productos_id);

            // Verifica si el producto ha sido escaneado
            if ($producto->estado_escaneo !== 'SI') {
                // Obtiene las entradas del producto para el cliente
                $entradas = EntradaProducto::where('clientes_id', $request->clientes_id)
                    ->where('productos_id', $request->productos_id)
                    ->get();

                // Calcula la deuda total
                $totalDeuda = $entradas->sum('total_deuda');

                return response()->json([
                    'status' => 'error',
                    'data' => [
                        'message' => 'El producto no ha sido escaneado en la entrada.',
                        'total_deuda' => $totalDeuda
                    ]
                ], 400);
            }

            // Verifica si el producto ya ha sido registrado como salido
            $existingSalida = SalidaRecogerProducto::where('productos_id', $request->productos_id)
                ->first();

            if ($existingSalida) {
                return response()->json([
                    'status' => 'error',
                    'data' => ['message' => 'El producto ya ha sido registrado como salido.']
                ], 400);
            }

            // Marca el producto como no disponible
            $producto->estadia = 'NO';
            $producto->save();

            // Crea la nueva salida
            $salida = SalidaRecogerProducto::create($request->all());

            return response()->json(['status' => 'success', 'data' => $salida], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'trabajadores_id' => 'required|exists:trabajadores,id',
            'productos_id' => 'required|exists:productos,id',
            'clientes_id' => 'required|exists:clientes,id',
            'camiones_id' => 'required|exists:camiones,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        try {
            $salida = SalidaRecogerProducto::findOrFail($id);
            $salida->update($request->all());
            return response()->json(['status' => 'success', 'data' => $salida], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $salida = SalidaRecogerProducto::findOrFail($id);
            $salida->delete();
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado con éxito.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
