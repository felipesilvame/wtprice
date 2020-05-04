<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSospechaRatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sospecha_ratas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tienda');
            $table->string('sku');
            $table->decimal('precio_referencia', 10, 2)->nullable();
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->decimal('precio_tarjeta', 10, 2)->nullable();
            $table->text('screenshot_url')->nullable();
            $table->float('porcentaje_rata')->nullable();
            $table->float('porcentaje_rata_relativo')->nullable();
            $table->string('nombre_tienda')->nullable();
            $table->string('nombre_producto')->nullable();
            $table->string('categoria')->nullable();
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
        Schema::dropIfExists('sospecha_ratas');
    }
}
