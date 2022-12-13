<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Prateleira extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'prateleiras';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento prateleira (N:1) estante.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estante()
    {
        return $this->belongsTo(Estante::class, 'estante_id', 'id');
    }

    /**
     * Relacionamento prateleira (1:N) caixas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caixas()
    {
        return $this->hasMany(Caixa::class, 'prateleira_id', 'id');
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

        $query->where(function (Builder $query) use ($termo) {
            $query->where('localidades.nome', 'like', $termo)
                ->orWhere('predios.nome', 'like', $termo)
                ->orWhere('andares.numero', 'like', $termo)
                ->orWhere('andares.apelido', 'like', $termo)
                ->orWhere('salas.numero', 'like', $termo)
                ->orWhere('estantes.numero', 'like', $termo)
                ->orWhere('prateleiras.numero', 'like', $termo);
        });
    }

    /**
     * Prateleira padrão para ser utilizada na hipótese de criação automática.
     *
     * @return self
     */
    public static function modeloPadrao()
    {
        $prateleira = new self();
        $prateleira->numero = 0;
        $prateleira->descricao = 'Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado';

        return $prateleira;
    }
}
