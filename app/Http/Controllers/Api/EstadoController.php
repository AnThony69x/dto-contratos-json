<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class EstadoController extends Controller
{
    //
    public function index(): JsonResponse 
    {
        return response()->json([
            'succes'=>true,
            'message'=> 'API de prueba en Laravel',
            'version'=>'1.0'
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Estado encontrado',
            'data' => [
                'id' => $id,
                'nombre' => 'Pendiente'
            ]
        ], 200);
    }
}
