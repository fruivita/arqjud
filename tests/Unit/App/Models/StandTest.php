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
    ['room_id', null, 'cannot be null'],                   // nonexistent
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

test('returns the stands using the default sort scope defined', function () {
    $first = 100;
    $second = 200;
    $third = 300;

    Stand::factory()->create(['number' => $third]);
    Stand::factory()->create(['number' => $first]);
    Stand::factory()->create(['number' => $second]);

    $stands = Stand::defaultOrder()->get();

    expect($stands->get(0)->number)->toBe($first)
    ->and($stands->get(1)->number)->toBe($second)
    ->and($stands->get(2)->number)->toBe($third);
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
    $stand = Stand::factory()->create();

    $stand->load('room.floor.building.site');

    expect($stand->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $stand->room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $stand->room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $stand->room->floor),
        __('Room') => route('archiving.register.room.show', $stand->room),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    $stand = Stand::factory()->create();

    $stand->load('room.floor.building.site');

    expect($stand->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $stand->room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $stand->room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $stand->room->floor),
        __('Room') => route('archiving.register.room.show', $stand->room),
        __('Stand') => route('archiving.register.stand.show', $stand),
    ]);
});
