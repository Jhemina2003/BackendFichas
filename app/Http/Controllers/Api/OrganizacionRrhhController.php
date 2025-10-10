<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Auth\RrhhService;
use Illuminate\Support\Facades\Log;

class OrganizacionRrhhController extends Controller
{

    /**
     * Listar sucursales (hijas) de una organización desde RRHH
     */
    public function sucursalesPorOrganizacion($id)
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'RREE-Aplicacion' => config('services.rrhh.aplicacion'),
            'RREE-ApiKey' => config('services.rrhh.api_key')
        ])->withOptions(['verify' => false])
        ->get('https://www.rree.gob.bo/backend/organizacion-interop/api/V2/unidades-organizacionales/hijas/' . $id);
        return response()->json($response->json());
    }
    private $rrhhService;

    public function __construct(RrhhService $rrhhService)
    {
        $this->rrhhService = $rrhhService;
    }

    /**
     * Listar organizaciones desde RRHH
     */
    public function listarOrganizaciones(Request $request)
    {
        $organizaciones = $this->rrhhService->listarOrganizaciones();
        return response()->json($organizaciones);
    }

    /**
     * Obtener detalle de una organización desde RRHH
     */
    public function detalleOrganizacion($id)
    {
        $organizacion = $this->rrhhService->getOrganizacion($id);
        return response()->json($organizacion);
    }

    /**
     * Listar usuarios por organización desde RRHH
     */
    public function usuariosPorOrganizacion($id)
    {
        $usuarios = $this->rrhhService->getUsuariosPorOrganizacion($id);
        return response()->json($usuarios);
    }

    /**
     * Listar todas las personas desde RRHH (general)
     */
    public function listarPersonas(Request $request)
    {
        $nombre = $request->input('nombre', 'a'); // Usar 'a' por defecto para obtener más resultados
        $personas = $this->rrhhService->buscarUsuariosPorNombre($nombre);
        
        $lista = [];
        if (isset($personas['lista']) && is_array($personas['lista'])) {
            foreach ($personas['lista'] as $persona) {
                $lista[] = [
                    'id' => $persona['id'] ?? null,
                    'nombre' => $persona['nombreCompleto'] ?? null
                ];
            }
        } elseif (isset($personas['data']) && is_array($personas['data'])) {
            foreach ($personas['data'] as $persona) {
                $lista[] = [
                    'id' => $persona['id'] ?? null,
                    'nombre' => $persona['nombreCompleto'] ?? null
                ];
            }
        }
        return response()->json($lista);
    }

    /**
     * Listar personas por organización desde RRHH
     */
    public function personasPorOrganizacion($id)
    {
        $personas = $this->rrhhService->getUsuariosPorOrganizacion($id);
        
        $lista = [];
        if (isset($personas['lista']) && is_array($personas['lista'])) {
            foreach ($personas['lista'] as $persona) {
                $lista[] = [
                    'id' => $persona['id'] ?? null,
                    'nombre' => $persona['nombreCompleto'] ?? null
                ];
            }
        } elseif (isset($personas['data']) && is_array($personas['data'])) {
            foreach ($personas['data'] as $persona) {
                $lista[] = [
                    'id' => $persona['id'] ?? null,
                    'nombre' => $persona['nombreCompleto'] ?? null
                ];
            }
        }
        return response()->json($lista);
    }

    /**
     * Buscar usuarios por nombre en RRHH
     */
    public function buscarUsuarios(Request $request)
    {
        $nombre = $request->input('nombre');
        $usuarios = $this->rrhhService->buscarUsuariosPorNombre($nombre);
        return response()->json($usuarios);
    }

    /**
     * Obtener detalle de usuario desde RRHH
     */
    public function detalleUsuario($id)
    {
        $usuario = $this->rrhhService->getUsuarioDetalle($id);
        return response()->json($usuario);
    }
}
