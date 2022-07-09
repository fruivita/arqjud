<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Não devem existir múltiplos registros, mas tão somente um único registro,
 * isto é, não haverá operação do tipo create, somente update.
 *
 * @see https://laravel.com/docs/9.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 * @see https://docs.microsoft.com/pt-br/windows/win32/adschema/a-samaccountname?redirectedfrom=MSDN
 */
return new class extends Migration {
    /**
     * Executa as migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->unsignedTinyInteger('id');
            $table->string('superadmin', 20);
            $table->timestamps();

            $table->primary('id');
        });
    }

    /**
     * Reverte as migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracoes');
    }
};
