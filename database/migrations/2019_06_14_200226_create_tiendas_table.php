<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTiendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tiendas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->string('nombre');
            $table->enum('protocolo', ['http', 'https'])->default('https');
            $table->string('prefix_api')->nullable();
            $table->string('suffix_api')->nullable();
            $table->json('headers')->nullable();
            $table->json('querystring')->nullable();
            $table->string('campo_nombre_producto')->nullable();
            $table->string('campo_precio_referencia')->nullable();
            $table->string('campo_precio_oferta')->nullable();
            $table->string('campo_precio_tarjeta')->nullable();
            $table->string('campo_request_error')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tiendas');
    }
}
