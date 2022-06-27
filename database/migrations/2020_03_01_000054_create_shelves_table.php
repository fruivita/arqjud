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
        Schema::create('shelves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stand_id');
            $table->unsignedInteger('number');
            $table->string('alias', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->unique([
                'stand_id',
                'number',
            ]);

            $table->unique([
                'stand_id',
                'alias',
            ]);

            $table
                ->foreign('stand_id')
                ->references('id')
                ->on('stands')
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
        Schema::dropIfExists('shelves');
    }
};
