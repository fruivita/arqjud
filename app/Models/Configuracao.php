<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Configuracao extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'configuracoes';

    /**
     * Indica se os IDs são auto incrementáveis.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Id persistido no banco de dado das configurações da aplicação.
     *
     * @var int
     */
    public const ID = 101;
}
