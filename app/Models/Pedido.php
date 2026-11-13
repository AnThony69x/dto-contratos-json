<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id', 'estado', 'metodo_pago', 'fecha_entrega', 'observacion', 'total',
    ];

    protected $casts = [
        'fecha_entrega' => 'date:Y-m-d',
        'total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }
}
