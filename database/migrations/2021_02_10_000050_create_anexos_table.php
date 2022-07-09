<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Court processes.
 *
 * @see https://laravel.com/docs/9.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 * @see https://atos.cnj.jus.br/atos/detalhar/119
 * @see https://www.conjur.com.br/dl/cnj-resolucao-651.pdf
 */
return new class extends Migration {
    /**
     * Executa as migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anexos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tipo_processo_id');
            $table->string('numero', 50)->unique();
            $table->dateTime('arquivado_em');
            $table->boolean('guarda_permanente')->default(false);
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table
                ->foreign('tipo_processo_id')
                ->references('id')
                ->on('tipos_processo')
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
        Schema::dropIfExists('anexos');
    }
};
