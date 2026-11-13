<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductoController extends Controller
{
    //
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lista de productos',
            'data' => [
                [
                    'id' => 1,
                    'nombre' => 'Torta de chocolate',
                    'precio' => 25.00
                ],
                [
                    'id' => 2,
                    'nombre' => 'Caja de chocolates',
                    'precio' => 15.00
                ]
            ]
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Producto encontrado',
            'data' => [
                'id' => $id,
                'nombre' => 'Torta de chocolate',
                'precio' => 25.00
            ]
        ], 200);
    }
}