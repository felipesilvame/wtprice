<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Updated 14-03-2020: imagen_url es ahora TEXT
 */
class CreateProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('id_tienda');
            $table->string('nombre');
            $table->string('sku');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('imagen_url')->nullable();
            $table->string('categoria')->nullable();
            $table->decimal('precio_referencia', 10, 2)->nullable();
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->decimal('precio_tarjeta', 10, 2)->nullable();
            $table->enum('estado', ['Activo', 'Detenido', 'Eliminado'])->default('Activo');
            $table->timestamps();
            $table->softDeletes();
            $table->index('uuid');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
}
