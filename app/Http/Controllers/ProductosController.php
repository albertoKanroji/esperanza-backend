<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Exception;

class ProductoController extends Controller
{
    public function index()
    {
        try {
            $data = Producto::all();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = Producto::create($request->all());
            return response()->json(['status' => 'success', 'data' => $data], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = Producto::findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->update($request->all());
            return response()->json(['status' => 'success', 'data' => $producto], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->delete();
            return response()->json(['status' => 'success', 'data' => 'Deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
}