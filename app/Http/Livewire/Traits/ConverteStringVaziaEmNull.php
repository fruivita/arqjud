<?php

namespace App\Http\Livewire\Traits;

/**
 * Trait para converter, nos componentes livewire, as propriedades públicas de
 * strings vazias para null.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 * @see https://github.com/livewire/livewire/issues/823
 */
trait ConverteStringVaziaEmNull
{
    /**
     * Nome das propriedades que devem ser ignoradas por essa trait, isto é,
     * não devem ser verificadas  e/ou convertidas.
     *
     * @var string[]
     */
    protected $exceto = [];

    /**
     * Converte strings vazias em null. Notar que antes da conversão, aplica-se
     * trim na string.
     *
     * A trait é acionada, automaticamente, no updated 'hook' de cada
     * propriedade.
     *
     * @param string $nome nome da propriedade
     * @param mixed $conteudo conteúdo/valor armazenado na propriedade
     *
     * @return void
     */
    public function updatedConverteStringVaziaEmNull(string $nome, $conteudo)
    {
        if (! is_string($conteudo) || in_array($nome, $this->exceto)) {
            return;
        }

        $conteudo = trim($conteudo);
        $conteudo = $conteudo === '' ? null : $conteudo;

        data_set($this, $nome, $conteudo);
    }
}
