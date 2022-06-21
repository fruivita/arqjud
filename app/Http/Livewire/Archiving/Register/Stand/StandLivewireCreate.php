<?php

namespace App\Http\Livewire\Archiving\Register\Stand;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Room;
use App\Models\Stand;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class StandLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Parent resource id.
     *
     * @var int
     */
    public int $room_id;

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
                "unique:stands,number,null,id,room_id,{$this->room_id}",
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
     * @param int $id parent resource id
     *
     * @return void
     */
    public function mount(int $id)
    {
        $this->room_id = $id;

        $this->stand = $this->blankModel();
    }

    /**
     * Computed property to get parent model.
     *
     * @return \App\Models\Room
     */
    public function getRoomProperty()
    {
        return Room::hierarchy()->findOrFail($this->room_id);
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
     * Computed property to list paginated stands based on room id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getStandsProperty()
    {
        return $this->applyPagination(
            Stand::hierarchy()
            ->where('stands.room_id', $this->room_id)
            ->orderByWhen($this->sort_column, $this->sort_direction)
        );
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

        $saved = $this->room->createStand($this->stand)
        ? true
        : false;

        $this->stand = $this->blankModel();

        $this->resetPage();

        $this->flashSelf($saved);
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Stand $stand
     *
     * @return void
     */
    public function markToDelete(Stand $stand)
    {
        $this->askForConfirmation($stand);
    }
}
