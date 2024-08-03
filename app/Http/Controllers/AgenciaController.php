<?php

namespace App\Http\Controllers;

use App\Models\Agencia;
use Illuminate\Http\Request;
use Exception;

class AgenciaController extends Controller
{
    public function index()
    {
        try {
            $data = Agencia::all();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = Agencia::create($request->all());
            return response()->json(['status' => 'success', 'data' => $data], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = Agencia::findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $agencia = Agencia::findOrFail($id);
            $agencia->update($request->all());
            return response()->json(['status' => 'success', 'data' => $agencia], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $agencia = Agencia::findOrFail($id);
            $agencia->delete();
            return response()->json(['status' => 'success', 'data' => 'Deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'data' => $e->getMessage()], 500);
        }
    }
}
