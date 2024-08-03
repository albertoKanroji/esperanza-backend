<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;
use Exception;

class AlmacenController extends Controller
{
    public function index()
    {
        try {
            $data = Almacen::all();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = Almacen::create($request->all());
            return response()->json(['status' => 'success', 'data' => $data], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = Almacen::findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $almacen = Almacen::findOrFail($id);
            $almacen->update($request->all());
            return response()->json(['status' => 'success', 'data' => $almacen], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $almacen = Almacen::findOrFail($id);
            $almacen->delete();
            return response()->json(['status' => 'success', 'data' => 'Deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
}