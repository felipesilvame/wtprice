<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTiendaAddSlugCompra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->string('url_prefix_compra')->nullable();
            $table->string('url_suffix_compra')->nullable();
            $table->string('campo_slug_compra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->dropColumn('url_prefix_compra');
            $table->dropColumn('url_suffix_compra');
            $table->dropColumn('campo_slug_compra');
        });
    }
}
