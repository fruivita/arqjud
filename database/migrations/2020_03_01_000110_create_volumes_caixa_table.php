<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @see https://laravel.com/docs/9.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 */
return new class extends Migration {
    /**
     * Executa as migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('volumes_caixa', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('caixa_id');
            $table->unsignedInteger('numero');
            $table->string('apelido', 100)->nullable();
            $table->timestamps();

            $table->unique([
                'caixa_id',
                'numero',
            ]);

            $table->unique([
                'caixa_id',
                'apelido',
            ]);

            $table
                ->foreign('caixa_id')
                ->references('id')
                ->on('caixas')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverte as migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('volumes_caixa');
    }
};
