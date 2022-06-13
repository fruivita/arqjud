<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\Shelf;
use App\Models\Stand;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create shelves in duplicate, that is, with the same numbers and stand', function () {
    $stand = Stand::factory()->create();

    expect(
        fn () => Shelf::factory(2)->create([
            'number' => 100,
            'stand_id' => $stand->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create shelf with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Shelf::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number',      -1,               'Out of range'],             // min 0
    ['number',      4294967296,       'Out of range'],             // max 4294967295
    ['number',     'foo',             'Incorrect integer value'],  // not convertible to integer
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Shelf::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['stand_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['stand_id', null, 'cannot be null'],                   // nonexistent
]);

// Happy path
test('create many shelves', function () {
    Shelf::factory(30)->create();

    expect(Shelf::count())->toBe(30);
});

test('fields in their minimum size are accepted', function () {
    Shelf::factory()->create(['number' => 0]);

    expect(Shelf::count())->toBe(1);
});

test('fields in their maximum size are accepted', function () {
    Shelf::factory()->create([
        'number' => 4294967295,
        'description' => Str::random(255),
    ]);

    expect(Shelf::count())->toBe(1);
});

test('optional fields are set', function () {
    Shelf::factory()->create(['description' => null]);

    expect(Shelf::count())->toBe(1);
});

test('returns the shelves using the default sort scope defined', function () {
    $first = 100;
    $second = 200;
    $third = 300;

    Shelf::factory()->create(['number' => $third]);
    Shelf::factory()->create(['number' => $first]);
    Shelf::factory()->create(['number' => $second]);

    $shelf = Shelf::defaultOrder()->get();

    expect($shelf->get(0)->number)->toBe($first)
    ->and($shelf->get(1)->number)->toBe($second)
    ->and($shelf->get(2)->number)->toBe($third);
});

test('one shelf belongs to one stand', function () {
    $stand = Stand::factory()->create();

    $shelf = Shelf::factory()
        ->for($stand, 'stand')
        ->create();

    $shelf->load(['stand']);

    expect($shelf->stand)->toBeInstanceOf(Stand::class);
});

test('one shelf has many boxes', function () {
    Shelf::factory()
        ->has(Box::factory(3), 'boxes')
        ->create();

    $shelf = Shelf::with('boxes')->first();

    expect($shelf->boxes)->toHaveCount(3);
});

test('parentLinks returns only show parents routes sorted from most distant to closest relationship if root is false', function () {
    $shelf = Shelf::factory()->create();

    $shelf->load('stand.room.floor.building.site');

    expect($shelf->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $shelf->stand->room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $shelf->stand->room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $shelf->stand->room->floor),
        __('Room') => route('archiving.register.room.show', $shelf->stand->room),
        __('Stand') => route('archiving.register.stand.show', $shelf->stand),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    $shelf = Shelf::factory()->create();

    $shelf->load('stand.room.floor.building.site');

    expect($shelf->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $shelf->stand->room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $shelf->stand->room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $shelf->stand->room->floor),
        __('Room') => route('archiving.register.room.show', $shelf->stand->room),
        __('Stand') => route('archiving.register.stand.show', $shelf->stand),
        __('Shelf') => route('archiving.register.shelf.show', $shelf),
    ]);
});
