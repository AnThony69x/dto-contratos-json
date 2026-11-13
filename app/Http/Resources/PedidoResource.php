<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public static $wrap = 'data';

    public function toArray(Request $request): array
    {
        return [
            'pedido_id' => $this->id,
            'estado' => $this->estado,
            'cliente_id' => $this->cliente_id,
            'metodo_pago' => $this->metodo_pago,
            'fecha_entrega' => $this->fecha_entrega->format('Y-m-d'),
            'observacion' => $this->observacion,                 // string|null (siempre presente)
            'items' => $this->items->map(fn ($i) => [
                'producto_id' => $i->producto_id,
                'cantidad' => $i->cantidad,
                'precio_unitario' => (string) $i->precio_unitario,  // decimal como string
                'subtotal' => (string) $i->subtotal,
            ])->values(),
            'total' => (string) $this->total,
            'creado_en' => $this->created_at->toIso8601String(),
        ];
    }
}
