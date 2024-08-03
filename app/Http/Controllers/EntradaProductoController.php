<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EntradaProducto;
use App\Models\Producto;
use Illuminate\Support\Facades\Validator;

class EntradaProductoController extends Controller
{
    public function index()
    {
        try {
            $entradas = EntradaProducto::with(['producto', 'trabajador', 'cliente'])->get();
            return response()->json(['status' => 'success', 'data' => $entradas], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $entrada = EntradaProducto::with(['producto', 'trabajador', 'cliente'])->findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $entrada], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productos_id' => 'required|exists:productos,id',
            'fecha_entrada' => 'required|date',
            'trabajadores_id' => 'required|exists:trabajadores,id',
            'precio_dia' => 'required|numeric',
            'total_deuda' => 'required|numeric',
            'clientes_id' => 'required|exists:clientes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        try {
            $producto = Producto::findOrFail($request->productos_id);

            if ($producto->estado_escaneo === 'NO') {
                $fechaEntrada = new \DateTime($request->fecha_entrada);
                $fechaHoy = new \DateTime();
                $diasTranscurridos = $fechaEntrada->diff($fechaHoy)->days;

                $totalDeudaCalculada = $diasTranscurridos * $request->precio_dia;

                // Verifica si ya existe un registro de EntradaProducto para este cliente y producto
                $entradaExistente = EntradaProducto::where('clientes_id', $request->clientes_id)
                    ->where('productos_id', $request->productos_id)
                    ->first();

                if ($entradaExistente) {
                    // Suma la deuda al total existente
                    $totalDeudaCalculada += $entradaExistente->total_deuda;
                }

                // Actualiza el total_deuda en el request
                $request->merge(['total_deuda' => $totalDeudaCalculada]);
            }

            $entrada = EntradaProducto::create($request->all());
            return response()->json(['status' => 'success', 'data' => $entrada], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'productos_id' => 'required|exists:productos,id',
            'fecha_entrada' => 'required|date',
            'trabajadores_id' => 'required|exists:trabajadores,id',
            'precio_dia' => 'required|numeric',
            'total_deuda' => 'required|numeric',
            'clientes_id' => 'required|exists:clientes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'data' => $validator->errors()], 400);
        }

        try {
            $entrada = EntradaProducto::findOrFail($id);
            $entrada->update($request->all());
            return response()->json(['status' => 'success', 'data' => $entrada], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $entrada = EntradaProducto::findOrFail($id);
            $entrada->delete();
            return response()->json(['status' => 'success', 'data' => null], 204);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
    public function getProductsByClient($clientes_id)
    {
        try {
            $productos = EntradaProducto::with('producto')
                ->where('clientes_id', $clientes_id)
                ->get()
                ->pluck('producto');

            return response()->json(['status' => 'success', 'data' => $productos], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
    public function getProductsByClientWithDebtSum($clientes_id)
    {
        try {
            $productos = EntradaProducto::where('clientes_id', $clientes_id)
                ->whereHas('producto', function ($query) {
                    $query->where('estado_deuda', 'NO');
                })
                ->with('producto')
                ->get();

            $totalDeuda = EntradaProducto::where('clientes_id', $clientes_id)
                ->sum('total_deuda');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'productos' => $productos,
                    'total_deuda' => $totalDeuda
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getProductsByClientWithSum($clientes_id)
    {
        try {
            $productos = EntradaProducto::where('clientes_id', $clientes_id)
                ->whereHas('producto', function ($query) {
                    $query->where('estado_deuda', 'SI');
                })
                ->with('producto')
                ->get();

            $totalDeuda = EntradaProducto::where('clientes_id', $clientes_id)
                ->sum('total_deuda');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'productos' => $productos,
                    'total_deuda' => $totalDeuda
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function showProducto($id)
    {

        $data = Producto::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }
}
