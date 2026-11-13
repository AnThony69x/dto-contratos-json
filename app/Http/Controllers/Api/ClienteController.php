<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lista de clientes',
            'data' => [
                [
                    'id' => 1,
                    'nombre' => 'Anthony Mejía',
                    'email' => 'anthony@example.com'
                ],
                [
                    'id' => 2,
                    'nombre' => 'María López',
                    'email' => 'maria@example.com'
                ]
            ]
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cliente encontrado',
            'data' => [
                'id' => $id,
                'nombre' => 'Anthony Mejía',
                'email' => 'anthony@example.com'
            ]
        ], 200);
    }
}