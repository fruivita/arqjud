<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
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
        Schema::create('tipos_processo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome', 100)->unique();
            $table->string('descricao', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverte as migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipos_processo');
    }
};
