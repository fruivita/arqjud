<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Floor;
use App\Models\Room;
use App\Models\Stand;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create rooms in duplicate, that is, with the same numbers and floor', function () {
    $floor = Floor::factory()->create();

    expect(
        fn () => Room::factory(2)->create([
            'number' => '100',
            'floor_id' => $floor->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create room with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Room::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number',      null,             'cannot be null'],           // required
    ['number',      Str::random(51),  'Data too long for column'], // maximum 50 characters
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Room::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['floor_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['floor_id', null, 'cannot be null'],                   // required
]);

// Happy path
test('create many rooms', function () {
    Room::factory(30)->create();

    expect(Room::count())->toBe(30);
});

test('fields in their maximum size are accepted', function () {
    Room::factory()->create([
        'number' => Str::random(50),
        'description' => Str::random(255),
    ]);

    expect(Room::count())->toBe(1);
});

test('optional fields are set', function () {
    Room::factory()->create(['description' => null]);

    expect(Room::count())->toBe(1);
});

test('createStand save the stand as a child of the room and create a default shelf', function () {
    $stand = new Stand();
    $stand->number = 10;
    $stand->description = 'foo';

    $room = Room::factory()->create();

    $room->createStand($stand);

    $room->load('stands.shelves');

    $stand = $room->stands->first();

    $shelf = $stand->shelves->first();

    expect($stand->number)->toBe(10)
    ->and($stand->description)->toBe('foo')
    ->and($stand->room_id)->toBe($room->id)
    ->and($shelf->number)->toBe(0)
    ->and($shelf->stand_id)->toBe($stand->id)
    ->and($shelf->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'));
});

test('one room belongs to one floor', function () {
    $floor = Floor::factory()->create();

    $room = Room::factory()
        ->for($floor, 'floor')
        ->create();

    $room->load(['floor']);

    expect($room->floor)->toBeInstanceOf(Floor::class);
});

test('one room has many stands', function () {
    Room::factory()
        ->has(Stand::factory(3), 'stands')
        ->create();

    $room = Room::with('stands')->first();

    expect($room->stands)->toHaveCount(3);
});

test('parentLinks returns only show parents routes sorted from most distant to closest relationship if root is false', function () {
    Room::factory()->create();

    $room = Room::hierarchy()->first();

    expect($room->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $room->site_id),
        __('Building') => route('archiving.register.building.show', $room->building_id),
        __('Floor') => route('archiving.register.floor.show', $room->floor_id),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    Room::factory()->create();

    $room = Room::hierarchy()->first();

    expect($room->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $room->site_id),
        __('Building') => route('archiving.register.building.show', $room->building_id),
        __('Floor') => route('archiving.register.floor.show', $room->floor_id),
        __('Room') => route('archiving.register.room.show', $room->id),
    ]);
});

test('parentLinks returns links based on hierarchical data present in the model or, if not, fetches them from the database', function () {
    Room::factory()->create();

    $room = Room::first();
    $room->load('floor.building');

    expect($room->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $room->floor->building->site_id),
        __('Building') => route('archiving.register.building.show', $room->floor->building_id),
        __('Floor') => route('archiving.register.floor.show', $room->floor_id),
        __('Room') => route('archiving.register.room.show', $room->id),
    ]);
});

test('hierarchy returns all rooms with the respective floor, building, site id and number/name and the number of stands of each', function () {
    Room::factory()->create(['number' => 10]);
    Room::factory()->has(Stand::factory(1), 'stands')->create(['number' => 20]);
    Room::factory()->has(Stand::factory(2), 'stands')->create(['number' => 30]);

    $all = Room::hierarchy()->get();

    $room_10 = $all->firstWhere('number', 10);
    $room_20 = $all->firstWhere('number', 20);
    $room_30 = $all->firstWhere('number', 30);

    expect($all)->toHaveCount(3)
    ->and(empty($room_10->site_id))->toBeFalse()
    ->and(empty($room_10->site_name))->toBeFalse()
    ->and(empty($room_10->building_id))->toBeFalse()
    ->and(empty($room_10->building_name))->toBeFalse()
    ->and(empty($room_10->floor_id))->toBeFalse()
    ->and(empty($room_10->floor_alias))->toBeFalse()
    ->and(empty($room_10->floor_number))->toBeFalse()
    ->and($room_10->stands_count)->toBe(0)
    ->and($room_20->stands_count)->toBe(1)
    ->and($room_30->stands_count)->toBe(2);
});
