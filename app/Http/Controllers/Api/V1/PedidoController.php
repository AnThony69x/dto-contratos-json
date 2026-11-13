<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pedidos\CrearPedidoRequest;
use App\Http\Resources\PedidoResource;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;

class PedidoController extends Controller
{
    public function __construct(private readonly PedidoService $servicio) {}

    /** POST /api/v1/pedidos -> 201 | 422 */
    public function store(CrearPedidoRequest $request): JsonResponse
    {
        $pedido = $this->servicio->crear($request->toDto());

        return (new PedidoResource($pedido))
            ->additional(['mensaje' => 'Pedido registrado correctamente'])
            ->response()
            ->setStatusCode(201);
    }
}
