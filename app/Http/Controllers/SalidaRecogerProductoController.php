<?php

namespace App\Http\Controllers;

use App\Models\SalidaRecogerProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\EntradaProducto;
use App\Models\Producto;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

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
    public function generateQrForSalida($productos_id)
    {
        try {
            // Encuentra el producto
            $producto = Producto::findOrFail($productos_id);

            // Verifica si el producto ha sido escaneado en la salida
            $existingSalida = SalidaRecogerProducto::where('productos_id', $productos_id)
                ->first();

            if ($existingSalida) {
                return response()->json([
                    'status' => 'error',
                    'data' => ['message' => 'El producto ya ha sido registrado como salido.']
                ], 400);
            }

            // Obtiene las entradas del producto para calcular la deuda
            $entradas = EntradaProducto::where('productos_id', $productos_id)->get();
            $totalDeuda = $entradas->sum('total_deuda');

            if ($totalDeuda > 0) {
                return response()->json([
                    'status' => 'error',
                    'data' => [
                        'message' => 'El producto tiene deuda pendiente.',
                        'total_deuda' => $totalDeuda,
                        'estado' => 'salida'
                    ]
                ], 400);
            }

            // Encuentra la entrada más reciente para obtener los datos necesarios
            $entradaProducto = EntradaProducto::where('productos_id', $productos_id)
                ->with(['cliente', 'trabajador'])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$entradaProducto || !$entradaProducto->cliente || !$entradaProducto->trabajador) {
                return response()->json([
                    'status' => 'error',
                    'data' => 'No se encontró la entrada del producto o faltan datos necesarios.'
                ], 400);
            }

            $cliente = $entradaProducto->cliente;
            $trabajador = $entradaProducto->trabajador;
            $camion = $producto->camiones_id; // Asumiendo que `camiones_id` es una columna en la tabla `productos`

            // Construye los datos en formato JSON
            $data = [
                'trabajadores_id' => $trabajador->id,
                'productos_id' => $producto->id,
                'clientes_id' => $cliente->id,
                'camiones_id' => $camion,
                'estado' => 'salida'
            ];

            // Convierte los datos a JSON
            $jsonData = json_encode($data);

            // Genera el código QR
            $qrCode = new QrCode($jsonData);
            $writer = new PngWriter();
            $qrCodeImage = $writer->write($qrCode);

            // Establece la respuesta con el código QR en formato PNG
            return response($qrCodeImage->getString(), 200)
                ->header('Content-Type', 'image/png');
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
