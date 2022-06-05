<?php

namespace App\Http\Livewire\Traits;

use App\Enums\Policy;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait for deleting resource.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithDeleteModel
{
    /**
     * Should the modal for deleting the resource be displayed?
     *
     * @var bool
     */
    public $show_delete_modal = false;

    /**
     * Resource that will be deleted.
     *
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    public ?Model $deleting = null;

    /**
     * Displays the modal for confirmation and defines the resource to be
     * deleted.
     *
     * @param \Illuminate\Database\Eloquent\Model $resource
     *
     * @return void
     */
    private function askForConfirmation(Model $resource)
    {
        $this->authorize(Policy::Delete->value, $resource);

        $this->deleting = $resource;

        $this->show_delete_modal = true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::Delete->value, $this->deleting);

        $deleted = $this->deleting->delete();

        $this->reset(['show_delete_modal', 'deleting']);

        $this->notify($deleted);
    }
}
