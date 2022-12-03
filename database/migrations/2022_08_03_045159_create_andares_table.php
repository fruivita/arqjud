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
        Schema::create('andares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('predio_id');
            $table->integer('numero');
            $table->string('apelido', 100)->nullable();
            $table->string('descricao', 255)->nullable();
            $table->timestamps();

            $table->unique(
                [
                    'predio_id',
                    'numero',
                ],
                'andares_unicos'
            );

            $table->unique(
                [
                    'predio_id',
                    'apelido',
                ],
                'andares_apelidos_unicos'
            );

            $table
                ->foreign('predio_id')
                ->references('id')
                ->on('predios')
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
        Schema::dropIfExists('andares');
    }
};
