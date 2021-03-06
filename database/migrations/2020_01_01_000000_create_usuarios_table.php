<?php

use App\Models\Lotacao;
use App\Models\Perfil;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @see https://laravel.com/docs/9.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 * @see https://docs.microsoft.com/pt-br/windows/win32/adschema/a-samaccountname?redirectedfrom=MSDN
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database
 */
return new class extends Migration {
    /**
     * Executa as migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lotacao_id')->default(Lotacao::SEM_LOTACAO);
            $table->unsignedBigInteger('funcao_confianca_id')->nullable();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->unsignedBigInteger('perfil_id')->default(Perfil::PADRAO);
            $table->unsignedBigInteger('perfil_concedido_por')->nullable();
            $table->unsignedBigInteger('antigo_perfil_id')->nullable();
            $table->string('nome', 255)->nullable();
            $table->string('username', 20)->unique();
            $table->string('password', 255)->nullable();
            $table->string('guid', 255)->unique()->nullable();
            $table->string('domain', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table
                ->foreign('lotacao_id')
                ->references('id')
                ->on('lotacoes')
                ->onUpdate('cascade');

            $table
                ->foreign('funcao_confianca_id')
                ->references('id')
                ->on('funcoes_confianca')
                ->onUpdate('cascade');

            $table
                ->foreign('cargo_id')
                ->references('id')
                ->on('cargos')
                ->onUpdate('cascade');

            $table
                ->foreign('perfil_id')
                ->references('id')
                ->on('perfis')
                ->onUpdate('cascade');

            $table
                ->foreign('perfil_concedido_por')
                ->references('id')
                ->on('usuarios')
                ->onUpdate('cascade');

            $table
                ->foreign('antigo_perfil_id')
                ->references('id')
                ->on('perfis')
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
        Schema::dropIfExists('usuarios');
    }
};
