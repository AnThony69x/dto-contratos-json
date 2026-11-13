<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cliente_id')->constrained('users');
            $t->string('estado', 20)->default('pendiente');
            $t->string('metodo_pago', 20);
            $t->date('fecha_entrega');
            $t->string('observacion', 255)->nullable();
            $t->decimal('total', 10, 2);
            $t->timestamps();
        });

        Schema::create('pedido_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $t->foreignId('producto_id')->constrained('productos');
            $t->unsignedInteger('cantidad');
            $t->decimal('precio_unitario', 10, 2);
            $t->decimal('subtotal', 10, 2);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
        Schema::dropIfExists('pedidos');
    }
};
