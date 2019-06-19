<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProductsAddIntervalo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
          $table->timestamp('ultima_actualizacion')->nullable();
          $table->unsignedInteger('intervalo_actualizacion')->default(60);
          $table->float('umbral_descuento',3,2)->nullable();
          $table->text('url_compra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
          $table->dropColumn('ultima_actualizacion');
          $table->dropColumn('intervalo_actualizacion');
          $table->dropColumn('umbral_descuento');
          $table->dropColumn('url_compra');
        });
    }
}
