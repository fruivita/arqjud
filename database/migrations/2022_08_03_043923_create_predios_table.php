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
        Schema::create('predios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('localidade_id');
            $table->string('nome', 100);
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique(
                [
                    'localidade_id',
                    'nome',
                ],
                'predios_unicos'
            );

            $table
                ->foreign('localidade_id')
                ->references('id')
                ->on('localidades')
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
        Schema::dropIfExists('predios');
    }
};
