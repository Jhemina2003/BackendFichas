<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organizacion;
use App\Services\OrganizacionService;

class OrganizacionController extends Controller
{
    protected $organizacionService;

    public function __construct(OrganizacionService $organizacionService)
    {
        $this->organizacionService = $organizacionService;
    }
    public function index(Request $request) {
        $query = Organizacion::query();
        if ($request->has('nombre')) {
            $query->where('nombre', 'like', '%'.$request->nombre.'%');
        }
        return $query->get();
    }
    public function show($id) { return Organizacion::findOrFail($id); }
    public function store(\App\Http\Requests\StoreOrganizacionRequest $request) {
        $organizacion = $this->organizacionService->crearOrganizacion($request->validated());
        return response()->json($organizacion, 201);
    }
    public function update(\App\Http\Requests\UpdateOrganizacionRequest $request, $id) {
        $organizacion = Organizacion::findOrFail($id);
        $organizacion = $this->organizacionService->actualizarOrganizacion($organizacion, $request->validated());
        return response()->json($organizacion);
    }
    public function destroy($id) { Organizacion::destroy($id); return response()->json(null, 204); }
}
