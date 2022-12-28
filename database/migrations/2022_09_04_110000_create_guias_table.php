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
            $table->string('remetente_username')->virtualAs("lower(json_unquote(json_extract(remetente, '$.username')))")->index();
            $table->string('remetente_nome')->virtualAs("lower(json_unquote(json_extract(remetente, '$.nome')))")->index();
            $table->jsonb('recebedor');
            $table->string('recebedor_username')->virtualAs("lower(json_unquote(json_extract(recebedor, '$.username')))")->index();
            $table->string('recebedor_nome')->virtualAs("lower(json_unquote(json_extract(recebedor, '$.nome')))")->index();
            $table->jsonb('lotacao_destinataria');
            $table->string('lotacao_destinataria_sigla')->virtualAs("lower(json_unquote(json_extract(lotacao_destinataria, '$.sigla')))")->index();
            $table->string('lotacao_destinataria_nome')->virtualAs("lower(json_unquote(json_extract(lotacao_destinataria, '$.nome')))")->index();
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
