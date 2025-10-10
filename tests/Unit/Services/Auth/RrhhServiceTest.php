<?php

namespace Tests\Unit\Services\Auth;

use Tests\TestCase;
use App\Services\Auth\RrhhService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RrhhServiceTest extends TestCase
{
    private $rrhhService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rrhhService = new RrhhService();
    }

    /** @test */
    public function puede_validar_usuario_exitosamente()
    {
        Http::fake([
            '*/autenticacion/api/token' => Http::response([
                'token' => 'valid-token-123',
                'expires_in' => 3600
            ], 200),
            '*/api/persona/*' => Http::response([
                'id' => 123,
                'nombres' => 'Juan Carlos',
                'apellidos' => 'Pérez González',
                'email' => 'juan.perez@rree.gob.bo',
                'activo' => true
            ], 200),
            '*/api/organizacion/*' => Http::response([
                'id' => 456,
                'nombre' => 'Unidad Apostilla y Legalizaciones',
                'sigla' => 'UAL'
            ], 200)
        ]);

        $result = $this->rrhhService->validateUser([
            'usuario' => 'jperez',
            'password' => 'password123'
        ]);

        $this->assertNotNull($result);
        $this->assertEquals('jperez', $result['usuario']);
        $this->assertEquals('Juan Carlos Pérez González', $result['nombre_completo']);
        $this->assertEquals('juan.perez@rree.gob.bo', $result['correo_electronico']);
        $this->assertTrue($result['activo']);
    }

    /** @test */
    public function retorna_null_con_credenciales_invalidas()
    {
        Http::fake([
            '*/autenticacion/api/token' => Http::response([
                'error' => 'Invalid credentials'
            ], 401)
        ]);

        $result = $this->rrhhService->validateUser([
            'usuario' => 'invalid_user',
            'password' => 'wrong_password'
        ]);

        $this->assertNull($result);
    }

    /** @test */
    public function maneja_errores_de_conexion_graciosamente()
    {
        Http::fake([
            '*/autenticacion/api/token' => Http::response(null, 500)
        ]);

        $result = $this->rrhhService->validateUser([
            'usuario' => 'jperez',
            'password' => 'password123'
        ]);

        $this->assertNull($result);
    }

    /** @test */
    public function puede_obtener_detalles_de_usuario()
    {
        Http::fake([
            '*/api/persona/*' => Http::response([
                'id' => 123,
                'nombres' => 'Juan Carlos',
                'apellidos' => 'Pérez González',
                'email' => 'juan.perez@rree.gob.bo',
                'activo' => true,
                'organizacion_id' => 456
            ], 200)
        ]);

        $result = $this->rrhhService->getUserDetails('jperez', ['token' => 'test-token']);

        $this->assertNotNull($result);
        $this->assertEquals('jperez', $result['usuario']);
        $this->assertEquals('Juan Carlos Pérez González', $result['nombre_completo']);
        $this->assertEquals('juan.perez@rree.gob.bo', $result['correo_electronico']);
        $this->assertEquals(123, $result['fk_persona_id']);
    }

    /** @test */
    public function puede_obtener_informacion_de_organizacion()
    {
        Http::fake([
            '*/api/organizacion/*' => Http::response([
                'id' => 456,
                'nombre' => 'Unidad Apostilla y Legalizaciones',
                'sigla' => 'UAL',
                'activo' => true
            ], 200)
        ]);

        $result = $this->rrhhService->getOrganizacion(456);

        $this->assertNotNull($result);
        $this->assertEquals(456, $result['id']);
        $this->assertEquals('Unidad Apostilla y Legalizaciones', $result['nombre']);
        $this->assertEquals('UAL', $result['sigla']);
    }

    /** @test */
    public function maneja_timeout_en_peticiones()
    {
        Http::fake([
            '*/autenticacion/api/token' => function () {
                throw new \GuzzleHttp\Exception\RequestException(
                    'Request timeout', 
                    new \GuzzleHttp\Psr7\Request('POST', 'test')
                );
            }
        ]);

        $result = $this->rrhhService->validateUser([
            'usuario' => 'jperez',
            'password' => 'password123'
        ]);

        $this->assertNull($result);
    }

    /** @test */
    public function logs_errores_de_autenticacion()
    {
        Log::shouldReceive('error')
            ->once()
            ->with('Error en autenticación RRHH', \Mockery::type('array'));

        Http::fake([
            '*/autenticacion/api/token' => Http::response(null, 500)
        ]);

        $this->rrhhService->validateUser([
            'usuario' => 'jperez',
            'password' => 'password123'
        ]);
    }

    /** @test */
    public function logs_autenticacion_exitosa()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Intentando autenticación RRHH', \Mockery::type('array'));

        Log::shouldReceive('info')
            ->once()
            ->with('Autenticación RRHH exitosa', \Mockery::type('array'));

        Http::fake([
            '*/autenticacion/api/token' => Http::response([
                'token' => 'valid-token-123'
            ], 200),
            '*/api/persona/*' => Http::response([
                'id' => 123,
                'nombres' => 'Juan',
                'apellidos' => 'Pérez',
                'email' => 'juan@test.com'
            ], 200)
        ]);

        $this->rrhhService->validateUser([
            'usuario' => 'jperez',
            'password' => 'password123'
        ]);
    }
}