<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Dominio;
use App\Models\Sesion;
use App\Models\Ficha;

class FichaNumeracionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear dominios necesarios para los tests
        $this->apostilla = Dominio::factory()->create(['nombre' => 'apostilla']);
        $this->legalizaciones = Dominio::factory()->create(['nombre' => 'legalizaciones']);
        $this->vivencia = Dominio::factory()->create(['nombre' => 'vivencia']);
        $this->devoluciones = Dominio::factory()->create(['nombre' => 'devoluciones']);
        $this->normal = Dominio::factory()->create(['nombre' => 'normal']);
        $this->prioritaria = Dominio::factory()->create(['nombre' => 'prioritaria']);
        $this->sesion = Sesion::factory()->create();
    }

    public function test_numeracion_por_tipo_servicio_y_tipo_ficha()
    {
        $fecha = now()->format('Y-m-d H:i:s');
        // APOS normal
        $response1 = $this->postJson('/api/fichas', [
            'fk_sesion_id' => $this->sesion->sesion_id,
            'tipo_ficha' => 'normal',
            'tipo_servicio' => 'apostilla',
            'fecha_inicio' => $fecha,
            'fecha_registro' => $fecha,
            'cantidad_llamadas' => 0
        ]);
        $response1->assertStatus(201);
        $this->assertEquals('APOS.1', $response1->json('numero_formateado'));

        // APOS prioritaria
        $response2 = $this->postJson('/api/fichas', [
            'fk_sesion_id' => $this->sesion->sesion_id,
            'tipo_ficha' => 'prioritaria',
            'tipo_servicio' => 'apostilla',
            'fecha_inicio' => $fecha,
            'fecha_registro' => $fecha,
            'cantidad_llamadas' => 0
        ]);
        $response2->assertStatus(201);
        $this->assertEquals('P.APOS.1', $response2->json('numero_formateado'));

        // LEGAL normal
        $response3 = $this->postJson('/api/fichas', [
            'fk_sesion_id' => $this->sesion->sesion_id,
            'tipo_ficha' => 'normal',
            'tipo_servicio' => 'legalizaciones',
            'fecha_inicio' => $fecha,
            'fecha_registro' => $fecha,
            'cantidad_llamadas' => 0
        ]);
        $response3->assertStatus(201);
        $this->assertEquals('LEGAL.1', $response3->json('numero_formateado'));

        // APOS normal (debe ser APOS.2)
        $response4 = $this->postJson('/api/fichas', [
            'fk_sesion_id' => $this->sesion->sesion_id,
            'tipo_ficha' => 'normal',
            'tipo_servicio' => 'apostilla',
            'fecha_inicio' => $fecha,
            'fecha_registro' => $fecha,
            'cantidad_llamadas' => 0
        ]);
        $response4->assertStatus(201);
        $this->assertEquals('APOS.2', $response4->json('numero_formateado'));
    }
}
