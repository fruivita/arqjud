<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @see https://laravel.com/docs/8.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 */
return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('box_volumes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('box_id');
            $table->unsignedInteger('number');
            $table->timestamps();

            $table->unique([
                'number',
                'box_id',
            ]);

            $table
                ->foreign('box_id')
                ->references('id')
                ->on('boxes')
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
        Schema::dropIfExists('box_volumes');
    }
};
