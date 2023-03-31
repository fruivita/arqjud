<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://laravel.com/docs/9.x/eloquent-mutators
 */
class Caixa extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'caixas';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero', 'ano', 'guarda_permanente', 'complemento', 'descricao', 'localidade_criadora_id', 'tipo_processo_id'];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'guarda_permanente' => 'boolean',
    ];

    /**
     * Relacionamento caixa (N:1) prateleira.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prateleira()
    {
        return $this->belongsTo(Prateleira::class, 'prateleira_id', 'id');
    }

    /**
     * Relacionamento caixa (1:N) processos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function processos()
    {
        return $this->hasMany(Processo::class, 'caixa_id', 'id');
    }

    /**
     * Relacionamento caixa (N:1) localidade.
     *
     * Localidade em que a caixa foi criada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function localidadeCriadora()
    {
        return $this->belongsTo(Localidade::class, 'localidade_criadora_id', 'id');
    }

    /**
     * Relacionamento caixa (N:1) tipo de processo.
     *
     * Tipo de processo que será guardado na caixa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipoProcesso()
    {
        return $this->belongsTo(TipoProcesso::class, 'tipo_processo_id', 'id');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where(function (Builder $query) use ($termo) {
            $query->where('localidades.nome', 'like', $termo)
                ->orWhere('predios.nome', 'like', $termo)
                ->orWhere('andares.numero', 'like', $termo)
                ->orWhere('andares.apelido', 'like', $termo)
                ->orWhere('salas.numero', 'like', $termo)
                ->orWhere('estantes.numero', 'like', $termo)
                ->orWhere('prateleiras.numero', 'like', $termo)
                ->orWhere('criadoras.nome', 'like', $termo)
                ->orWhere('tipos_processo.nome', 'like', $termo)
                ->orWhere('caixas.numero', 'like', $termo)
                ->orWhere('caixas.ano', 'like', $termo)
                ->orWhere('caixas.complemento', 'like', $termo);
        });
    }

    /**
     * Atualiza a caixa e altera o status de guarda permanente dos processos de
     * acordo com o valor definido para a caixa.
     *
     * @return bool
     */
    public function atualizar()
    {
        try {
            DB::beginTransaction();

            $this->save();

            $this
                ->processos()
                ->update(['guarda_permanente' => $this->guarda_permanente]);

            DB::commit();

            return true;
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error(
                __('Falha na atualização da caixa'),
                [
                    'caixa' => $this,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    /**
     * Move os processos informados para a caixa instanciada.
     *
     * Todos os processos movidos assumirão o status de guarda permanente
     * definido na caixa de destino.
     *
     * @param  string[]  $numeros número dos processos
     * @return int quantidade de registros afetados ou falso boolean zero se
     * não houver nenhuma alteração.
     */
    public function moverProcessos(array $numeros)
    {
        return Processo::whereIn('numero', $numeros)
            ->update([
                'guarda_permanente' => $this->guarda_permanente,
                'caixa_id' => $this->id,
            ]);
    }
}
