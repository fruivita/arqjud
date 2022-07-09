<?php

namespace App\Http\Livewire\Traits;

use App\Enums\Policy;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait para operações de exclusão.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait ComExclusao
{
    /**
     * Deve-se exibir o modal para exclusão do item?
     *
     * @var bool
     */
    public $exibir_modal_exclusao = false;

    /**
     * Item que será excluído.
     *
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    public ?Model $excluir = null;

    /**
     * Exibe o modal de confirmação e define o item que será excluído.
     *
     * @param \Illuminate\Database\Eloquent\Model $item
     *
     * @return void
     */
    private function confirmarExclusao(Model $item)
    {
        $this->authorize(Policy::Delete->value, $item);

        $this->excluir = $item;

        $this->exibir_modal_exclusao = true;
    }

    /**
     * Remove o item do storage.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::Delete->value, $this->excluir);

        $excluido = $this->excluir->delete();

        $this->reset(['exibir_modal_exclusao', 'excluir']);

        $this->notificar($excluido);
    }
}
