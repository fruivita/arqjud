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
class Estante extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'estantes';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento estante (N:1) sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class, 'sala_id', 'id');
    }

    /**
     * Relacionamento estante (1:N) prateleiras.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prateleiras()
    {
        return $this->hasMany(Prateleira::class, 'estante_id', 'id');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * Pressupõe join com as tabelas:
     * - Localidades;
     * - Prédios;
     * - Andares;
     * - Salas.
     *
     * Colunas pesquisadas:
     * - nome da localidade;
     * - nome do prédio;
     * - número do andar;
     * - apelido do andar;
     * - número da sala;
     * - número da estante.
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
            ->orWhere('salas.numero', 'like', $termo)
            ->orWhere('estantes.numero', 'like', $termo);
    }

    /**
     * Estante padrão para ser utilizada na hipótese de criação automática.
     *
     * @return self
     */
    public static function modeloPadrao()
    {
        $estante = new self();
        $estante->numero = 0;
        $estante->descricao = 'Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado';

        return $estante;
    }

    /**
     * Cria a estante com os parâmetros informados. Também cria a prateleira
     * padrão.
     *
     * A prateleira padrão é a que não foi revisada ou criada por requisição
     * direta e intencional do usuário, mas automaticamente criada pela
     * aplicação como efeito colateral de uma ação.
     *
     * @param  string  $numero número da estante
     * @param  int  $sala_pai id da sala pai
     * @param  string|null  $descricao descrição da estante
     * @return bool
     */
    public static function criar(string $numero, int $sala_pai, string $descricao = null)
    {
        $estante = new self();

        try {
            DB::beginTransaction();

            $estante->numero = $numero;
            $estante->descricao = $descricao;
            $estante->sala_id = $sala_pai;
            $estante->save();

            $estante->prateleiras()->save(Prateleira::modeloPadrao());

            DB::commit();

            return true;
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error(
                __('Falha na criação da estante'),
                [
                    'params' => ['numero' => $numero, 'sala' => $sala_pai, 'descricao' => $descricao],
                    'estante' => $estante,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }
}
