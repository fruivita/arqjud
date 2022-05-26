<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @see https://laravel.com/docs/migrations
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
        Schema::create('rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('floor_id');
            $table->unsignedInteger('number');
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->unique([
                'floor_id',
                'number',
            ]);

            $table
                ->foreign('floor_id')
                ->references('id')
                ->on('floors')
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
        Schema::dropIfExists('rooms');
    }
};
