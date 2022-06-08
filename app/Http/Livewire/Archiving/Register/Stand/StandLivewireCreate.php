<?php

namespace App\Http\Livewire\Archiving\Register\Stand;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Stand;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class StandLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;

    /**
     * Parent resource.
     *
     * @var \App\Models\Room
     */
    public Room $room;

    /**
     * Resource that will be created.
     *
     * @var \App\Models\Stand
     */
    public Stand $stand;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'stand.number' => [
                'bail',
                'required',
                'integer',
                'between:1,100000',
                "unique:stands,number,null,id,room_id,{$this->room->id}",
            ],

            'stand.description' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'stand.number' => __('Stand'),
            'stand.description' => __('Description'),
        ];
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Create->value, Stand::class);
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mount()
    {
        $this->room->load('floor.building.site');
        $this->stand = $this->blankModel();
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Stand
     */
    private function blankModel()
    {
        return new Stand();
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.stand.create')->layout('layouts.app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $saved = $this->room->stands()->save($this->stand)
        ? true
        : false;

        $this->stand = $this->blankModel();

        $this->flashSelf($saved);
    }
}
