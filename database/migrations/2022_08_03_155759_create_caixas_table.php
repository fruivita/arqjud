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
        Schema::create('caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prateleira_id');
            $table->unsignedBigInteger('localidade_criadora_id');
            $table->unsignedInteger('numero');
            $table->unsignedSmallInteger('ano');
            // Destinada a processos de guarda permanente?
            $table->boolean('guarda_permanente');
            // Trata-se de complemento da numeração
            $table->string('complemento', 50)->nullable();
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique(
                [
                    'numero',
                    'ano',
                    'guarda_permanente',
                    'complemento',
                    'localidade_criadora_id',
                ],
                'caixas_unicas'
            );

            $table
                ->foreign('prateleira_id')
                ->references('id')
                ->on('prateleiras')
                ->onUpdate('cascade');

            $table
                ->foreign('localidade_criadora_id')
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
        Schema::dropIfExists('caixas');
    }
};
