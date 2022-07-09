<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @see https://laravel.com/docs/9.x/migrations
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
        Schema::create('lotacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('lotacao_pai')->nullable();
            $table->string('nome', 255);
            $table->string('sigla', 50);
            $table->timestamps();

            $table->primary('id');

            $table
                ->foreign('lotacao_pai')
                ->references('id')
                ->on('lotacoes')
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
        Schema::dropIfExists('lotacoes');
    }
};
