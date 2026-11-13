<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Catálogo mínimo: el servidor toma de aquí el precio de cada ítem del pedido.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 120);
            $t->decimal('precio', 10, 2);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
