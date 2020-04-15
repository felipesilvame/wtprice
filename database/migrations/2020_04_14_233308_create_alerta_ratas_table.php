<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertaRatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alerta_ratas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_tienda');
            $table->unsignedBigInteger('id_producto');
            $table->decimal('precio_antes', 10, 2)->nullable();
            $table->decimal('precio_oferta_antes', 10, 2)->nullable();
            $table->decimal('precio_tarjeta_antes', 10, 2)->nullable();
            $table->decimal('precio_ahora', 10, 2)->nullable();
            $table->decimal('precio_oferta_ahora', 10, 2)->nullable();
            $table->decimal('precio_tarjeta_ahora', 10, 2)->nullable();
            $table->text('screenshot_url')->nullable();
            $table->float('porcentaje_rata')->nullable();
            $table->float('porcentaje_rata_relativo')->nullable();
            $table->string('nombre_tienda')->nullable();
            $table->string('nombre_producto')->nullable();
            $table->text('url_compra')->nullable();
            $table->text('url_imagen')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alerta_ratas');
    }
}
