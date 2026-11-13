<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lista de categorías',
            'data' => [
                [
                    'id' => 1,
                    'nombre' => 'Repostería'
                ],
                [
                    'id' => 2,
                    'nombre' => 'Detalles'
                ],
                [
                    'id' => 3,
                    'nombre' => 'Sublimación'
                ]
            ]
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Categoría encontrada',
            'data' => [
                'id' => $id,
                'nombre' => 'Repostería'
            ]
        ], 200);
    }
}