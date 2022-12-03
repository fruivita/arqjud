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
        Schema::create('guias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('numero');
            $table->unsignedSmallInteger('ano');
            $table->dateTime('gerada_em');
            $table->jsonb('remetente');
            $table->jsonb('recebedor');
            $table->jsonb('lotacao_destinataria');
            $table->jsonb('processos');
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique(
                [
                    'numero',
                    'ano',
                ],
                'guias_unicas'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guias');
    }
};
