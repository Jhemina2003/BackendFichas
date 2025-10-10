<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Usuario;
use App\Models\Sucursal;
use App\Models\Organizacion;
use App\Services\Auth\RrhhService;
use App\Services\Auth\SignInService;
use Illuminate\Support\Facades\Http;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear organización y sucursal para las pruebas
        $organizacion = Organizacion::factory()->create([
            'nombre' => 'Unidad Apostilla y Legalizaciones'
        ]);
        
        Sucursal::factory()->create([
            'fk_organizacion_id' => $organizacion->organizacion_id,
            'nombre' => 'Santa Cruz'
        ]);
    }

    /** @test */
    public function puede_autenticar_usuario_con_rrhh_exitoso()
    {
        // Mock de respuesta exitosa del RRHH
        Http::fake([
            '*/autenticacion/api/token' => Http::response([
                'token' => 'test-token-123',
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

        $response = $this->postJson('/api/login', [
            'usuario' => 'jperez',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user' => [
                            'usuario_id',
                            'usuario',
                            'nombre_completo',
                            'correo_electronico',
                            'sucursal',
                            'ventanilla'
                        ],
                        'token',
                        'token_type'
                    ]
                ]);

        // Verificar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('usuarios', [
            'usuario' => 'jperez',
            'nombre_completo' => 'Juan Carlos Pérez González',
            'correo_electronico' => 'juan.perez@rree.gob.bo'
        ]);
    }

    /** @test */
    public function falla_autenticacion_con_credenciales_invalidas()
    {
        // Mock de respuesta de error del RRHH
        Http::fake([
            '*/autenticacion/api/token' => Http::response([
                'error' => 'Invalid credentials'
            ], 401)
        ]);

        $response = $this->postJson('/api/login', [
            'usuario' => 'usuario_invalido',
            'password' => 'password_incorrecto'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Error de autenticación'
                ]);
    }

    /** @test */
    public function puede_cerrar_sesion_exitosamente()
    {
        $usuario = Usuario::factory()->create();
        $token = $usuario->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Sesión cerrada exitosamente'
                ]);

        // Verificar que el token fue eliminado
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $usuario->usuario_id,
            'tokenable_type' => Usuario::class
        ]);
    }

    /** @test */
    public function puede_obtener_informacion_del_usuario_autenticado()
    {
        $usuario = Usuario::factory()->create();
        $token = $usuario->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user' => [
                            'usuario_id',
                            'usuario',
                            'nombre_completo',
                            'sucursal',
                            'ventanilla'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function puede_renovar_token_exitosamente()
    {
        $usuario = Usuario::factory()->create();
        $token = $usuario->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/refresh');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'token',
                        'token_type'
                    ]
                ]);

        // Verificar que el token anterior fue eliminado
        $this->assertEquals(1, $usuario->tokens()->count());
    }

    /** @test */
    public function actualiza_usuario_existente_en_nueva_autenticacion()
    {
        // Crear usuario existente
        $usuario = Usuario::factory()->create([
            'usuario' => 'jperez',
            'nombre_completo' => 'Juan Pérez',
            'correo_electronico' => 'juan.viejo@rree.gob.bo'
        ]);

        // Mock de respuesta del RRHH con datos actualizados
        Http::fake([
            '*/autenticacion/api/token' => Http::response([
                'token' => 'test-token-123',
                'expires_in' => 3600
            ], 200),
            '*/api/persona/*' => Http::response([
                'id' => 123,
                'nombres' => 'Juan Carlos',
                'apellidos' => 'Pérez González',
                'email' => 'juan.nuevo@rree.gob.bo',
                'activo' => true
            ], 200),
            '*/api/organizacion/*' => Http::response([
                'id' => 456,
                'nombre' => 'Unidad Apostilla y Legalizaciones',
                'sigla' => 'UAL'
            ], 200)
        ]);

        $response = $this->postJson('/api/login', [
            'usuario' => 'jperez',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);

        // Verificar que los datos del usuario fueron actualizados
        $this->assertDatabaseHas('usuarios', [
            'usuario_id' => $usuario->usuario_id,
            'usuario' => 'jperez',
            'nombre_completo' => 'Juan Carlos Pérez González',
            'correo_electronico' => 'juan.nuevo@rree.gob.bo'
        ]);
    }

    /** @test */
    public function valida_campos_requeridos_en_login()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['usuario', 'password']);
    }

    /** @test */
    public function requiere_autenticacion_para_rutas_protegidas()
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);

        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);

        $response = $this->postJson('/api/refresh');
        $response->assertStatus(401);
    }
}