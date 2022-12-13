<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Sala extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'salas';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento sala (N:1) andar.
     *
     * Andar da sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function andar()
    {
        return $this->belongsTo(Andar::class, 'andar_id', 'id');
    }

    /**
     * Relacionamento sala (1:N) estantes.
     *
     * Estantes da sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function estantes()
    {
        return $this->hasMany(Estante::class, 'sala_id', 'id');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where('localidades.nome', 'like', $termo)
            ->orWhere('predios.nome', 'like', $termo)
            ->orWhere('andares.numero', 'like', $termo)
            ->orWhere('andares.apelido', 'like', $termo)
            ->orWhere('salas.numero', 'like', $termo);
    }

    /**
     * Cria a sala com os parâmetros informados. Também cria a estante e a
     * prateleira padrão.
     *
     * A estante e/ou a prateleira padrão é a que não foi revisada ou criada
     * por requisição direta e intencional do usuário, mas automaticamente
     * criada pela aplicação como efeito colateral de uma ação.
     *
     * @param  string  $numero número da sala
     * @param  \App\Models\Andar  $andar andar pai
     * @param  string|null  $descricao descrição da sala
     * @return bool
     */
    public static function criar(string $numero, Andar $andar, string $descricao = null)
    {
        $sala = new self();

        try {
            DB::beginTransaction();

            $sala->numero = $numero;
            $sala->descricao = $descricao;

            $andar
                ->salas()->save($sala)
                ->estantes()->save(Estante::modeloPadrao())
                ->prateleiras()->save(Prateleira::modeloPadrao());

            DB::commit();

            return true;
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error(
                __('Falha na criação da sala'),
                [
                    'params' => ['numero' => $numero, 'andar' => $andar, 'descricao' => $descricao],
                    'sala' => $sala,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }
}
