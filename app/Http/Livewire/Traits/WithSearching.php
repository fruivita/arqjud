<?php

namespace App\Http\Livewire\Traits;

use Illuminate\Support\Facades\Validator;

/**
 * Trait designed group search behaviour.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithSearching
{
    /**
     * Searchable term entered by the user.
     *
     * @var string
     */
    public $term;

    /**
     * Get custom attributes for query strings.
     *
     * @return array<string, mixed>
     */
    protected function queryString()
    {
        return [
            'term' => [
                'except' => '',
                'as' => 's',
            ],
        ];
    }

    /**
     * Returns the pagination to the initial pagination.
     *
     * Runs before a property called $term is updated.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function updatingTerm($value)
    {
        Validator::make(
            data: ['term' => $value],
            rules: ['term' => ['nullable', 'string', 'max:50']],
            customAttributes: ['term' => __('Searchable term')]
        )->validate();

        $this->resetPage();
    }
}
