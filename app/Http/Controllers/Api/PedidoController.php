<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PedidoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lista de pedidos',
            'data' => [
                [
                    'id' => 1,
                    'cliente' => 'Anthony Mejía',
                    'total' => 45.00,
                    'estado' => 'Pendiente'
                ],
                [
                    'id' => 2,
                    'cliente' => 'María López',
                    'total' => 30.00,
                    'estado' => 'Entregado'
                ]
            ]
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pedido encontrado',
            'data' => [
                'id' => $id,
                'cliente' => 'Anthony Mejía',
                'total' => 45.00,
                'estado' => 'Pendiente'
            ]
        ], 200);
    }
}