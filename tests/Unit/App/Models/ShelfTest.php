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
    ['stand_id', null, 'cannot be null'],                   // required
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

test('zero is a valid value for the shelf number', function () {
    Shelf::factory()->create(['number' => 0]);

    $shelf = Shelf::first();

    expect($shelf->number)->toBe(0);
});

test('uninformedShelf returns the model with the expected attributes', function () {
    $shelf = Shelf::uninformedShelf();

    expect($shelf->number)->toBe(0)
    ->and($shelf->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'));
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
    Shelf::factory()->create();

    $shelf = Shelf::hierarchy()->first();

    expect($shelf->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $shelf->site_id),
        __('Building') => route('archiving.register.building.show', $shelf->building_id),
        __('Floor') => route('archiving.register.floor.show', $shelf->floor_id),
        __('Room') => route('archiving.register.room.show', $shelf->room_id),
        __('Stand') => route('archiving.register.stand.show', $shelf->stand_id),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    Shelf::factory()->create();

    $shelf = Shelf::hierarchy()->first();

    expect($shelf->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $shelf->site_id),
        __('Building') => route('archiving.register.building.show', $shelf->building_id),
        __('Floor') => route('archiving.register.floor.show', $shelf->floor_id),
        __('Room') => route('archiving.register.room.show', $shelf->room_id),
        __('Stand') => route('archiving.register.stand.show', $shelf->stand_id),
        __('Shelf') => route('archiving.register.shelf.show', $shelf->id),
    ]);
});

test('parentLinks returns links based on hierarchical data present in the model or, if not, fetches them from the database', function () {
    Shelf::factory()->create();

    $shelf = Shelf::first();
    $shelf->load('stand.room.floor.building');

    expect($shelf->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $shelf->stand->room->floor->building->site_id),
        __('Building') => route('archiving.register.building.show', $shelf->stand->room->floor->building_id),
        __('Floor') => route('archiving.register.floor.show', $shelf->stand->room->floor_id),
        __('Room') => route('archiving.register.room.show', $shelf->stand->room->id),
        __('Stand') => route('archiving.register.stand.show', $shelf->stand_id),
        __('Shelf') => route('archiving.register.shelf.show', $shelf->id),
    ]);
});

test('hierarchy returns all shelves with the respective stand, room, floor, building, site id and number/name and the number of boxes of each', function () {
    Shelf::factory()->create(['number' => 10]);
    Shelf::factory()->has(Box::factory(1), 'boxes')->create(['number' => 20]);
    Shelf::factory()->has(Box::factory(2), 'boxes')->create(['number' => 30]);

    $all = Shelf::hierarchy()->get();

    $shelf_10 = $all->firstWhere('number', 10);
    $shelf_20 = $all->firstWhere('number', 20);
    $shelf_30 = $all->firstWhere('number', 30);

    expect($all)->toHaveCount(3)
    ->and(empty($shelf_10->site_id))->toBeFalse()
    ->and(empty($shelf_10->site_name))->toBeFalse()
    ->and(empty($shelf_10->building_id))->toBeFalse()
    ->and(empty($shelf_10->building_name))->toBeFalse()
    ->and(empty($shelf_10->floor_id))->toBeFalse()
    ->and(empty($shelf_10->floor_alias))->toBeFalse()
    ->and(empty($shelf_10->floor_number))->toBeFalse()
    ->and(empty($shelf_10->room_id))->toBeFalse()
    ->and(empty($shelf_10->room_number))->toBeFalse()
    ->and(empty($shelf_10->stand_id))->toBeFalse()
    ->and(empty($shelf_10->stand_number))->toBeFalse()
    ->and($shelf_10->boxes_count)->toBe(0)
    ->and($shelf_20->boxes_count)->toBe(1)
    ->and($shelf_30->boxes_count)->toBe(2);
});

test('forHumans returns data in human-readable format', function () {
    $stand = Stand::factory()->create(['number' => 10]);
    Shelf::factory()->for($stand, 'stand')->create(['number' => 100]);

    $shelf = Shelf::hierarchy()->first();

    expect($shelf->for_humans)->toBe(100)
    ->and($shelf->stand_for_humans)->toBe(10);
});

test('forHumans returns "Uninformed" if the stand or shelf number is zero', function () {
    $stand = Stand::factory()->create(['number' => 0]);
    Shelf::factory()->for($stand, 'stand')->create(['number' => 0]);

    $shelf = Shelf::hierarchy()->first();

    expect($shelf->for_humans)->toBe(__('Uninformed'))
    ->and($shelf->stand_for_humans)->toBe(__('Uninformed'));
});
