<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Shelf;
use App\Models\Stand;
use Illuminate\Database\QueryException;

// Exceptions
test('throws exception when trying to create boxes in duplicate, that is, with the same numbers and year', function () {
    expect(
        fn () => Box::factory(2)->create([
            'number' => 100,
            'year' => 2020,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create box with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Box::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number', -1,         'Out of range'],             // min 0
    ['number', 4294967296, 'Out of range'],             // max 4294967295
    ['number', 'foo',      'Incorrect integer value'],  // not convertible to integer
    ['year',   -1,         'Out of range value'],       // min 0
    ['year',   65536,      'Out of range value'],       // max 65536
    ['year',   'foo',      'Incorrect integer value'],  // not convertible to integer
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Box::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['shelf_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['shelf_id', null, 'cannot be null'],                   // required
]);

// Happy path
test('create many boxes', function () {
    Box::factory(30)->create();

    expect(Box::count())->toBe(30);
});

test('fields in their maximum size are accepted', function () {
    Box::factory()->create([
        'number' => 4294967295,
        'year' => 65535,
    ]);

    expect(Box::count())->toBe(1);
});

test('one box belongs to one shelf', function () {
    $shelf = Shelf::factory()->create();

    $box = Box::factory()
        ->for($shelf, 'shelf')
        ->create();

    $box->load(['shelf']);

    expect($box->shelf)->toBeInstanceOf(Shelf::class);
});

test('one box has many box volumes', function () {
    Box::factory()
        ->has(BoxVolume::factory(3), 'volumes')
        ->create();

    $box = Box::with('volumes')->first();

    expect($box->volumes)->toHaveCount(3);
});

test('nextVolumeNumber return the volume number to be used to create the new box volume', function () {
    $box = Box::factory()
        ->has(BoxVolume::factory()->state(['number' => 10]), 'volumes')
        ->create();

    expect($box->nextVolumeNumber())->toBe(11);
});

test('nextBoxNumber return the box number to be used to create the new box', function () {
    Box::factory()->create(['year' => 2020, 'number' => 30]);
    Box::factory()->create(['year' => 2020, 'number' => 20]);

    expect(Box::nextBoxNumber(2020))->toBe(31)
    ->and(Box::nextBoxNumber(2021))->toBe(1);
});

test('parentLinks returns only edit parents routes sorted from most distant to closest relationship if root is false', function () {
    Box::factory()->create();

    $box = Box::hierarchy()->first();

    expect($box->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.edit', $box->site_id),
        __('Building') => route('archiving.register.building.edit', $box->building_id),
        __('Floor') => route('archiving.register.floor.edit', $box->floor_id),
        __('Room') => route('archiving.register.room.edit', $box->room_id),
        __('Stand') => route('archiving.register.stand.edit', $box->stand_id),
        __('Shelf') => route('archiving.register.shelf.edit', $box->shelf_id),
    ]);
});

test('parentLinks returns edit parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    Box::factory()->create();

    $box = Box::hierarchy()->first();

    expect($box->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.edit', $box->site_id),
        __('Building') => route('archiving.register.building.edit', $box->building_id),
        __('Floor') => route('archiving.register.floor.edit', $box->floor_id),
        __('Room') => route('archiving.register.room.edit', $box->room_id),
        __('Stand') => route('archiving.register.stand.edit', $box->stand_id),
        __('Shelf') => route('archiving.register.shelf.edit', $box->shelf_id),
        __('Box') => route('archiving.register.box.edit', $box->id),
    ]);
});

test('parentLinks returns links based on hierarchical data present in the model or, if not, fetches them from the database', function () {
    Box::factory()->create();

    $box = Box::first();
    $box->load('shelf.stand.room.floor.building');

    expect($box->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.edit', $box->shelf->stand->room->floor->building->site_id),
        __('Building') => route('archiving.register.building.edit', $box->shelf->stand->room->floor->building_id),
        __('Floor') => route('archiving.register.floor.edit', $box->shelf->stand->room->floor_id),
        __('Room') => route('archiving.register.room.edit', $box->shelf->stand->room->id),
        __('Stand') => route('archiving.register.stand.edit', $box->shelf->stand_id),
        __('Shelf') => route('archiving.register.shelf.edit', $box->shelf->id),
        __('Box') => route('archiving.register.box.edit', $box->id),
    ]);
});

test('createMany method creates and persists sequential boxes with equal attributes and sequential boxes', function () {
    $template = Box::factory()->makeOne(['number' => 10]);
    $shelf = Shelf::factory()->create();

    Box::createMany($template, 30, 5, $shelf);

    $boxes = Box::with('volumes')->get();

    $box = $boxes->random();

    expect($boxes)->toHaveCount(30)
    ->and($shelf->load('boxes')->boxes)->toHaveCount(30)
    ->and($boxes->first()->number)->toBe(10)
    ->and($boxes->last()->number)->toBe(39)
    ->and($box->year)->toBe($template->year)
    ->and($box->volumes)->toHaveCount(5)
    ->and($box->volumes->first()->number)->toBe(1)
    ->and($box->volumes->last()->number)->toBe(5);
});

test('hierarchy returns all boxes with the respective shelf, stand, room, floor, building, site id and number/name and the number of volumes of each', function () {
    Box::factory()->create(['number' => 10]);
    Box::factory()->has(BoxVolume::factory(1), 'volumes')->create(['number' => 20]);
    Box::factory()->has(BoxVolume::factory(2), 'volumes')->create(['number' => 30]);

    $all = Box::hierarchy()->get();

    $box_10 = $all->firstWhere('number', 10);
    $box_20 = $all->firstWhere('number', 20);
    $box_30 = $all->firstWhere('number', 30);

    expect($all)->toHaveCount(3)
    ->and(empty($box_10->site_id))->toBeFalse()
    ->and(empty($box_10->site_name))->toBeFalse()
    ->and(empty($box_10->building_id))->toBeFalse()
    ->and(empty($box_10->building_name))->toBeFalse()
    ->and(empty($box_10->floor_id))->toBeFalse()
    ->and(empty($box_10->floor_alias))->toBeFalse()
    ->and(empty($box_10->floor_number))->toBeFalse()
    ->and(empty($box_10->room_id))->toBeFalse()
    ->and(empty($box_10->room_number))->toBeFalse()
    ->and(empty($box_10->stand_id))->toBeFalse()
    ->and(empty($box_10->stand_number))->toBeFalse()
    ->and(empty($box_10->shelf_id))->toBeFalse()
    ->and(empty($box_10->shelf_number))->toBeFalse()
    ->and($box_10->volumes_count)->toBe(0)
    ->and($box_20->volumes_count)->toBe(1)
    ->and($box_30->volumes_count)->toBe(2);
});

test('forHumans returns data in human-readable format', function () {
    $stand = Stand::factory()->create(['number' => 10]);
    $shelf = Shelf::factory()->for($stand, 'stand')->create(['number' => 100]);
    Box::factory()->for($shelf, 'shelf')->create(['number' => 1000, 'year' => 2020]);

    $box = Box::hierarchy()->first();

    expect($box->for_humans)->toBe('1000/2020')
    ->and($box->stand_for_humans)->toBe(10)
    ->and($box->shelf_for_humans)->toBe(100);
});

test('forHumans returns "Uninformed" if the stand or shelf number is zero', function () {
    $stand = Stand::factory()->create(['number' => 0]);
    $shelf = Shelf::factory()->for($stand, 'stand')->create(['number' => 0]);
    Box::factory()->for($shelf, 'shelf')->create();

    $box = Box::hierarchy()->first();

    expect($box->stand_for_humans)->toBe(__('Uninformed'))
    ->and($box->shelf_for_humans)->toBe(__('Uninformed'));
});
