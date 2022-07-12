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
        Schema::create('estantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sala_id');
            $table->unsignedInteger('numero');
            $table->string('apelido', 100)->nullable();
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique(
                [
                    'sala_id',
                    'numero',
                ],
                'estantes_unicas'
            );

            $table->unique([
                'sala_id',
                'apelido',
            ]);

            $table
                ->foreign('sala_id')
                ->references('id')
                ->on('salas')
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
        Schema::dropIfExists('estantes');
    }
};
