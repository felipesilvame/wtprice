<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAlertaRataAddSegundoAviso extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alerta_ratas', function (Blueprint $table) {
            $table->enum('disponible', ['Disponible', 'Sin Stock', 'Sin Información'])->default('Sin Información');
            $table->integer('stock')->nullable();
            $table->boolean('segunda_notificacion')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alerta_ratas', function (Blueprint $table) {
            $table->dropColumn('disponible');
            $table->dropColumn('stock');
            $table->dropColumn('segunda_notifiacion');
        });
    }
}
