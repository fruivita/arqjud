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
        Schema::create('caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prateleira_id');
            $table->unsignedInteger('numero');
            $table->unsignedSmallInteger('ano');
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique([
                'numero',
                'ano',
            ]);

            $table
                ->foreign('prateleira_id')
                ->references('id')
                ->on('prateleiras')
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
        Schema::dropIfExists('caixas');
    }
};
