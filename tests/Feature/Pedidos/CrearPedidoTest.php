<?php

namespace Tests\Feature\Pedidos;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CrearPedidoTest extends TestCase
{
    use RefreshDatabase;

    private User $cliente;

    private function payload(array $override = []): array
    {
        return array_replace([
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'tarjeta',
            'fecha_entrega' => now()->addDay()->format('Y-m-d'),
            'observacion' => 'Pedido de prueba',
            'items' => [['producto_id' => 1, 'cantidad' => 2]],
        ], $override);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cliente = User::factory()->create();
        DB::table('productos')->insert([
            ['id' => 1, 'nombre' => 'Producto demo', 'precio' => '12.50'],
            ['id' => 4, 'nombre' => 'Producto demo 2', 'precio' => '0.10'],
        ]);
    }

    /** P1: JSON válido -> 201 + estructura del contrato de respuesta */
    public function test_p1_json_valido_devuelve_201(): void
    {
        $this->postJson('/api/v1/pedidos', $this->payload())
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['pedido_id', 'estado', 'cliente_id', 'metodo_pago', 'fecha_entrega',
                    'observacion', 'items' => [['producto_id', 'cantidad', 'precio_unitario', 'subtotal']],
                    'total', 'creado_en'],
                'mensaje',
            ])
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.items.0.precio_unitario', '12.50')
            ->assertJsonPath('data.items.0.subtotal', '25.00')
            ->assertJsonPath('data.total', '25.00')
            ->assertJsonPath('mensaje', 'Pedido registrado correctamente');

        $this->assertDatabaseCount('pedidos', 1);
        $this->assertDatabaseCount('pedido_items', 1);
    }

    /** P1b: observacion omitida -> siempre presente como null; total exacto sin error de flotante (3 x 0.10) */
    public function test_p1b_observacion_null_y_total_exacto(): void
    {
        $datos = $this->payload(['items' => [['producto_id' => 4, 'cantidad' => 3]]]);
        unset($datos['observacion']);

        $this->postJson('/api/v1/pedidos', $datos)
            ->assertCreated()
            ->assertJsonPath('data.observacion', null)
            ->assertJsonPath('data.total', '0.30');
    }

    /** P2: falta un campo obligatorio -> 422 con detalle */
    public function test_p2_falta_campo_obligatorio_devuelve_422(): void
    {
        $datos = $this->payload();
        unset($datos['metodo_pago']);

        $this->postJson('/api/v1/pedidos', $datos)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['metodo_pago' => 'El campo metodo_pago es obligatorio.'])
            ->assertJsonPath('message', 'El campo metodo_pago es obligatorio.');

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pedido_items', 0);
    }

    /** P3: tipo de dato incorrecto -> error controlado y nada persistido */
    public function test_p3_tipo_incorrecto_devuelve_422_y_no_persiste(): void
    {
        $this->postJson('/api/v1/pedidos', $this->payload([
            'cliente_id' => 'abc',
            'items' => [['producto_id' => 1, 'cantidad' => 'dos']],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cliente_id', 'items.0.cantidad']);

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pedido_items', 0);
    }
}
