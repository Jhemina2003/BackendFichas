<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Services\SucursalService;

class SucursalController extends Controller
{
    public function index(Request $request) {
        $query = Sucursal::query();
        if ($request->has('organizacion_id')) {
            $query->where('fk_organizacion_id', $request->organizacion_id);
        }
        if ($request->has('nombre')) {
            $query->where('nombre', 'like', '%'.$request->nombre.'%');
        }
        return $query->get();
    }
    public function show($id) { return Sucursal::findOrFail($id); }
    public function store(\App\Http\Requests\StoreSucursalRequest $request, SucursalService $sucursalService) {
        try {
            $sucursal = $sucursalService->crearSucursal($request->validated());
            if ($sucursal->wasRecentlyCreated === false) {
                return response()->json(['error' => 'La sucursal ya existe en el sistema.'], 409);
            }
            return response()->json($sucursal, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function update(\App\Http\Requests\UpdateSucursalRequest $request, $id, SucursalService $sucursalService) {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal = $sucursalService->actualizarSucursal($sucursal, $request->validated());
        return response()->json($sucursal);
    }
    public function destroy($id) { Sucursal::destroy($id); return response()->json(null, 204); }
}
