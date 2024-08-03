<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use Illuminate\Http\Request;
use Exception;

class TrabajadorController extends Controller
{
    public function index()
    {
        try {
            $data = Trabajador::all();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = Trabajador::create($request->all());
            return response()->json(['status' => 'success', 'data' => $data], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = Trabajador::findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $trabajador = Trabajador::findOrFail($id);
            $trabajador->update($request->all());
            return response()->json(['status' => 'success', 'data' => $trabajador], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $trabajador = Trabajador::findOrFail($id);
            $trabajador->delete();
            return response()->json(['status' => 'success', 'data' => 'Deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
}
