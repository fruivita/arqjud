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
        Schema::create('prateleiras', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('estante_id');
            $table->unsignedInteger('numero');
            $table->string('apelido', 100)->nullable();
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique([
                'estante_id',
                'numero',
            ]);

            $table->unique(
                [
                    'estante_id',
                    'apelido',
                ],
                'prateleiras_unicas'
            );

            $table
                ->foreign('estante_id')
                ->references('id')
                ->on('estantes')
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
        Schema::dropIfExists('prateleiras');
    }
};
