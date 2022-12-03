<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see https://laravel.com/docs/9.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 * @see https://atos.cnj.jus.br/atos/detalhar/119
 * @see https://atos.cnj.jus.br/files/resolucao_65_16122008_04032013165912.pdf
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
        Schema::create('processos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('volume_caixa_id');
            $table->unsignedBigInteger('processo_pai_id')->nullable();
            $table->string('numero', 20)->unique();
            //opção 1: NNNNNNNDDAAAAJTROOOO
            //opção 2: AAAASSLLNNNNNND
            //opção 3: AANNNNNNND
            $table->string('numero_antigo', 20)->unique()->nullable();
            $table->date('arquivado_em');
            $table->boolean('guarda_permanente');
            $table->unsignedInteger('qtd_volumes');
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table
                ->foreign('processo_pai_id')
                ->references('id')
                ->on('processos')
                ->onUpdate('cascade');

            $table
                ->foreign('volume_caixa_id')
                ->references('id')
                ->on('volumes_caixa')
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
        Schema::dropIfExists('processos');
    }
};
