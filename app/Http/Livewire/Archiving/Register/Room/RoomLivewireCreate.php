<?php

namespace App\Http\Livewire\Archiving\Register\Room;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class RoomLivewireCreate extends Component
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
    public int $floor_id;

    /**
     * Resource that will be created.
     *
     * @var \App\Models\Room
     */
    public Room $room;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'room.number' => [
                'bail',
                'required',
                'integer',
                'between:1,100000',
                "unique:rooms,number,null,id,floor_id,{$this->floor_id}",
            ],

            'room.description' => [
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
            'room.number' => __('Room'),
            'room.description' => __('Description'),
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
        $this->authorize(Policy::Create->value, Room::class);
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
        $this->floor_id = $id;

        $this->room = $this->blankModel();
    }

    /**
     * Computed property to get parent model.
     *
     * @return \App\Models\Floor
     */
    public function getFloorProperty()
    {
        return Floor::hierarchy()->findOrFail($this->floor_id);
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Room
     */
    private function blankModel()
    {
        return new Room();
    }

    /**
     * Computed property to list paginated rooms based on floor id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRoomsProperty()
    {
        return $this->applyPagination(
            Room::hierarchy()
            ->where('rooms.floor_id', $this->floor_id)
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
        return view('livewire.archiving.register.room.create')->layout('layouts.app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $saved = $this->floor->createRoom($this->room)
        ? true
        : false;

        $this->room = $this->blankModel();

        $this->resetPage();

        $this->flashSelf($saved);
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Room $room
     *
     * @return void
     */
    public function setToDelete(Room $room)
    {
        $this->askForConfirmation($room);
    }
}
