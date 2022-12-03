<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see https://laravel.com/docs/9.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sala_id');
            $table->string('numero', 50);
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique(
                [
                    'sala_id',
                    'numero',
                ],
                'estantes_unicas'
            );

            $table
                ->foreign('sala_id')
                ->references('id')
                ->on('salas')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estantes');
    }
};
