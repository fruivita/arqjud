<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Room;
use App\Models\Shelf;
use App\Models\Stand;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create stands in duplicate, that is, with the same numbers and room', function () {
    $room = Room::factory()->create();

    expect(
        fn () => Stand::factory(2)->create([
            'number' => 100,
            'room_id' => $room->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create stand with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Stand::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number',      -1,               'Out of range'],             // min 0
    ['number',      4294967296,       'Out of range'],             // max 4294967295
    ['number',     'foo',             'Incorrect integer value'],  // not convertible to integer
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Stand::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['room_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['room_id', null, 'cannot be null'],                   // required
]);

// Happy path
test('create many stands', function () {
    Stand::factory(30)->create();

    expect(Stand::count())->toBe(30);
});

test('fields in their minimum size are accepted', function () {
    Stand::factory()->create(['number' => 0]);

    expect(Stand::count())->toBe(1);
});

test('fields in their maximum size are accepted', function () {
    Stand::factory()->create([
        'number' => 4294967295,
        'description' => Str::random(255),
    ]);

    expect(Stand::count())->toBe(1);
});

test('optional fields are set', function () {
    Stand::factory()->create(['description' => null]);

    expect(Stand::count())->toBe(1);
});

test('zero is a valid value for the stand number', function () {
    Stand::factory()->create(['number' => 0]);

    $stand = Stand::first();

    expect($stand->number)->toBe(0);
});

test('uninformedStand returns the model with the expected attributes', function () {
    $stand = Stand::uninformedStand();

    expect($stand->number)->toBe(0)
    ->and($stand->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'));
});

test('one stand belongs to one room', function () {
    $room = Room::factory()->create();

    $stand = Stand::factory()
        ->for($room, 'room')
        ->create();

    $stand->load(['room']);

    expect($stand->room)->toBeInstanceOf(Room::class);
});

test('one stand has many shelves', function () {
    Stand::factory()
        ->has(Shelf::factory(3), 'shelves')
        ->create();

    $stand = Stand::with('shelves')->first();

    expect($stand->shelves)->toHaveCount(3);
});

test('parentLinks returns only show parents routes sorted from most distant to closest relationship if root is false', function () {
    Stand::factory()->create();

    $stand = Stand::hierarchy()->first();

    expect($stand->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $stand->site_id),
        __('Building') => route('archiving.register.building.show', $stand->building_id),
        __('Floor') => route('archiving.register.floor.show', $stand->floor_id),
        __('Room') => route('archiving.register.room.show', $stand->room_id),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    Stand::factory()->create();

    $stand = Stand::hierarchy()->first();

    expect($stand->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $stand->site_id),
        __('Building') => route('archiving.register.building.show', $stand->building_id),
        __('Floor') => route('archiving.register.floor.show', $stand->floor_id),
        __('Room') => route('archiving.register.room.show', $stand->room_id),
        __('Stand') => route('archiving.register.stand.show', $stand->id),
    ]);
});

test('parentLinks returns links based on hierarchical data present in the model or, if not, fetches them from the database', function () {
    Stand::factory()->create();

    $stand = Stand::first();
    $stand->load('room.floor.building');

    expect($stand->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $stand->room->floor->building->site_id),
        __('Building') => route('archiving.register.building.show', $stand->room->floor->building_id),
        __('Floor') => route('archiving.register.floor.show', $stand->room->floor_id),
        __('Room') => route('archiving.register.room.show', $stand->room->id),
        __('Stand') => route('archiving.register.stand.show', $stand->id),
    ]);
});

test('hierarchy returns all stands with the respective room, floor, building, site id and number/name and the number of shelves of each', function () {
    Stand::factory()->create(['number' => 10]);
    Stand::factory()->has(Shelf::factory(1), 'shelves')->create(['number' => 20]);
    Stand::factory()->has(Shelf::factory(2), 'shelves')->create(['number' => 30]);

    $all = Stand::hierarchy()->get();

    $stand_10 = $all->firstWhere('number', 10);
    $stand_20 = $all->firstWhere('number', 20);
    $stand_30 = $all->firstWhere('number', 30);

    expect($all)->toHaveCount(3)
    ->and(empty($stand_10->site_id))->toBeFalse()
    ->and(empty($stand_10->site_name))->toBeFalse()
    ->and(empty($stand_10->building_id))->toBeFalse()
    ->and(empty($stand_10->building_name))->toBeFalse()
    ->and(empty($stand_10->floor_id))->toBeFalse()
    ->and(empty($stand_10->floor_number))->toBeFalse()
    ->and(empty($stand_10->room_id))->toBeFalse()
    ->and(empty($stand_10->room_number))->toBeFalse()
    ->and($stand_10->shelves_count)->toBe(0)
    ->and($stand_20->shelves_count)->toBe(1)
    ->and($stand_30->shelves_count)->toBe(2);
});

test('forHumans returns data in human-readable format', function () {
    Stand::factory()->create(['number' => 10]);

    $stand = Stand::hierarchy()->first();

    expect($stand->for_humans)->toBe(10);
});

test('forHumans returns "Uninformed" if the stand or shelf number is zero', function () {
    Stand::factory()->create(['number' => 0]);

    $stand = Stand::hierarchy()->first();

    expect($stand->for_humans)->toBe(__('Uninformed'));
});
