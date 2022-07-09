<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @see https://laravel.com/docs/migrations
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
        Schema::create('salas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('andar_id');
            $table->string('numero', 50);
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique([
                'andar_id',
                'numero',
            ]);

            $table
                ->foreign('andar_id')
                ->references('id')
                ->on('andares')
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
        Schema::dropIfExists('salas');
    }
};
