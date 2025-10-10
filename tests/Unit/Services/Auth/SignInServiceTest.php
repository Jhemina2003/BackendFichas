<?php

namespace Tests\Unit\Services\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Auth\SignInService;
use App\Services\Auth\RrhhService;
use App\Models\Usuario;
use App\Models\Sucursal;
use App\Models\Organizacion;
use Illuminate\Support\Facades\Hash;

class SignInServiceTest extends TestCase
{
    use RefreshDatabase;

    private $signInService;
    private $rrhhServiceMock;

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

        $this->rrhhServiceMock = \Mockery::mock(RrhhService::class);
        $this->signInService = new SignInService($this->rrhhServiceMock);
    }

    /** @test */
    public function puede_autenticar_nuevo_usuario()
    {
        $personaData = [
            'usuario' => 'jperez',
            'nombre_completo' => 'Juan Carlos Pérez González',
            'correo_electronico' => 'juan.perez@rree.gob.bo',
            'fk_persona_id' => 123,
            'activo' => true,
            'organizacion_nombre' => 'Unidad Apostilla y Legalizaciones'
        ];

        $this->rrhhServiceMock
            ->shouldReceive('validateUser')
            ->once()
            ->with(['usuario' => 'jperez', 'password' => 'password123'])
            ->andReturn($personaData);

        $result = $this->signInService->authenticate([
            'usuario' => 'jperez',
            'password' => 'password123'
        ]);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertEquals('Bearer', $result['token_type']);

        // Verificar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('usuarios', [
            'usuario' => 'jperez',
            'nombre_completo' => 'Juan Carlos Pérez González',
            'correo_electronico' => 'juan.perez@rree.gob.bo'
        ]);
    }

    /** @test */
    public function puede_actualizar_usuario_existente()
    {
        // Crear usuario existente
        $usuario = Usuario::factory()->create([
            'usuario' => 'jperez',
            'nombre_completo' => 'Juan Pérez',
            'correo_electronico' => 'juan.viejo@test.com'
        ]);

        $personaDataActualizada = [
            'usuario' => 'jperez',
            'nombre_completo' => 'Juan Carlos Pérez González',
            'correo_electronico' => 'juan.nuevo@rree.gob.bo',
            'fk_persona_id' => 123,
            'activo' => true
        ];

        $this->rrhhServiceMock
            ->shouldReceive('validateUser')
            ->once()
            ->andReturn($personaDataActualizada);

        $result = $this->signInService->authenticate([
            'usuario' => 'jperez',
            'password' => 'newpassword'
        ]);

        // Verificar que los datos del usuario fueron actualizados
        $usuarioActualizado = Usuario::where('usuario', 'jperez')->first();
        $this->assertEquals('Juan Carlos Pérez González', $usuarioActualizado->nombre_completo);
        $this->assertEquals('juan.nuevo@rree.gob.bo', $usuarioActualizado->correo_electronico);
        $this->assertTrue(Hash::check('newpassword', $usuarioActualizado->password));
    }

    /** @test */
    public function falla_con_credenciales_invalidas()
    {
        $this->rrhhServiceMock
            ->shouldReceive('validateUser')
            ->once()
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Credenciales inválidas');

        $this->signInService->authenticate([
            'usuario' => 'invalid',
            'password' => 'wrong'
        ]);
    }

    /** @test */
    public function falla_con_credenciales_vacias()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usuario y contraseña son requeridos');

        $this->signInService->authenticate([
            'usuario' => '',
            'password' => ''
        ]);
    }

    /** @test */
    public function puede_cerrar_sesion()
    {
        $usuario = Usuario::factory()->create();
        $token = $usuario->createToken('test-token')->plainTextToken;

        $result = $this->signInService->logout($usuario);

        $this->assertTrue($result);
        $this->assertEquals(0, $usuario->tokens()->count());
    }

    /** @test */
    public function puede_renovar_token()
    {
        $usuario = Usuario::factory()->create();
        $tokenAnterior = $usuario->createToken('old-token')->plainTextToken;

        $nuevoToken = $this->signInService->refreshToken($usuario);

        $this->assertNotEmpty($nuevoToken);
        $this->assertNotEquals($tokenAnterior, $nuevoToken);
        $this->assertEquals(1, $usuario->tokens()->count());
    }

    /** @test */
    public function determina_sucursal_correctamente()
    {
        $organizacion = Organizacion::factory()->create([
            'nombre' => 'Ministerio de Relaciones Exteriores'
        ]);
        
        $sucursal = Sucursal::factory()->create([
            'fk_organizacion_id' => $organizacion->organizacion_id,
            'nombre' => 'La Paz'
        ]);

        $personaData = [
            'usuario' => 'testuser',
            'nombre_completo' => 'Test User',
            'correo_electronico' => 'test@test.com',
            'organizacion_nombre' => 'Ministerio de Relaciones Exteriores'
        ];

        $this->rrhhServiceMock
            ->shouldReceive('validateUser')
            ->once()
            ->andReturn($personaData);

        $result = $this->signInService->authenticate([
            'usuario' => 'testuser',
            'password' => 'password'
        ]);

        $usuario = Usuario::where('usuario', 'testuser')->first();
        $this->assertEquals($sucursal->sucursal_id, $usuario->fk_sucursal_id);
    }

    /** @test */
    public function asigna_sucursal_por_defecto_cuando_no_encuentra_organizacion()
    {
        $personaData = [
            'usuario' => 'testuser',
            'nombre_completo' => 'Test User',
            'correo_electronico' => 'test@test.com',
            'organizacion_nombre' => 'Organización No Existente'
        ];

        $this->rrhhServiceMock
            ->shouldReceive('validateUser')
            ->once()
            ->andReturn($personaData);

        $result = $this->signInService->authenticate([
            'usuario' => 'testuser',
            'password' => 'password'
        ]);

        $usuario = Usuario::where('usuario', 'testuser')->first();
        $this->assertNotNull($usuario->fk_sucursal_id);
        
        // Debe haber asignado la primera sucursal disponible
        $sucursalPorDefecto = Sucursal::first();
        $this->assertEquals($sucursalPorDefecto->sucursal_id, $usuario->fk_sucursal_id);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}