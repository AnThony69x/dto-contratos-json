<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Pedidos\CrearPedidoDTO;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

class PedidoService
{
    public function crear(CrearPedidoDTO $dto): Pedido
    {
        return DB::transaction(function () use ($dto) {
            $precios = DB::table('productos')
                ->whereIn('id', array_map(fn ($i) => $i->productoId, $dto->items))
                ->pluck('precio', 'id');

            $totalCent = 0;
            $lineas = [];
            foreach ($dto->items as $item) {
                $precioCent = (int) round(((float) $precios[$item->productoId]) * 100);
                $subtotalCent = $precioCent * $item->cantidad;
                $totalCent += $subtotalCent;
                $lineas[] = [
                    'producto_id' => $item->productoId,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => self::decimal($precioCent),
                    'subtotal' => self::decimal($subtotalCent),
                ];
            }

            $pedido = Pedido::create([
                'cliente_id' => $dto->clienteId,
                'estado' => 'pendiente',
                'metodo_pago' => $dto->metodoPago,
                'fecha_entrega' => $dto->fechaEntrega,
                'observacion' => $dto->observacion,
                'total' => self::decimal($totalCent),
            ]);
            $pedido->items()->createMany($lineas);

            return $pedido->load('items');
        });
    }

    /** 2500 -> "25.00" */
    private static function decimal(int $centavos): string
    {
        return sprintf('%d.%02d', intdiv($centavos, 100), $centavos % 100);
    }
}
