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
        Schema::create('solicitacoes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('processo_id');
            $table->unsignedBigInteger('solicitante_id');
            $table->unsignedBigInteger('recebedor_id')->nullable();
            $table->unsignedBigInteger('remetente_id')->nullable();
            $table->unsignedBigInteger('rearquivador_id')->nullable();
            $table->unsignedBigInteger('destino_id');
            $table->unsignedBigInteger('guia_id')->nullable();
            $table->dateTime('solicitada_em');
            $table->dateTime('entregue_em')->nullable();
            $table->dateTime('devolvida_em')->nullable();
            $table->boolean('por_guia');
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table
                ->foreign('processo_id')
                ->references('id')
                ->on('processos')
                ->onUpdate('cascade');

            $table
                ->foreign('solicitante_id')
                ->references('id')
                ->on('usuarios')
                ->onUpdate('cascade');

            $table
                ->foreign('recebedor_id')
                ->references('id')
                ->on('usuarios')
                ->onUpdate('cascade');

            $table
                ->foreign('remetente_id')
                ->references('id')
                ->on('usuarios')
                ->onUpdate('cascade');

            $table
                ->foreign('rearquivador_id')
                ->references('id')
                ->on('usuarios')
                ->onUpdate('cascade');

            $table
                ->foreign('destino_id')
                ->references('id')
                ->on('lotacoes')
                ->onUpdate('cascade');

            $table
                ->foreign('guia_id')
                ->references('id')
                ->on('guias')
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
        Schema::dropIfExists('solicitacoes');
    }
};
