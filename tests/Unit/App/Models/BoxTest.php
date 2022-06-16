<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Shelf;
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

test('numberForHumans returns the number and year of the box ready for display', function () {
    $box = Box::factory()->create(['number' => 100, 'year' => 2020]);

    expect($box->numberForHumans())->toBe('100/2020');
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

test('parentLinks returns only show parents routes sorted from most distant to closest relationship if root is false', function () {
    $box = Box::factory()->create();

    $box->load('shelf.stand.room.floor.building.site');

    expect($box->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $box->shelf->stand->room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $box->shelf->stand->room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $box->shelf->stand->room->floor),
        __('Room') => route('archiving.register.room.show', $box->shelf->stand->room),
        __('Stand') => route('archiving.register.stand.show', $box->shelf->stand),
        __('Shelf') => route('archiving.register.shelf.show', $box->shelf),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    $box = Box::factory()->create();

    $box->load('shelf.stand.room.floor.building.site');

    expect($box->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $box->shelf->stand->room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $box->shelf->stand->room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $box->shelf->stand->room->floor),
        __('Room') => route('archiving.register.room.show', $box->shelf->stand->room),
        __('Stand') => route('archiving.register.stand.show', $box->shelf->stand),
        __('Shelf') => route('archiving.register.shelf.show', $box->shelf),
        __('Box') => route('archiving.register.box.show', $box),
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

test('hierarchy returns all boxes with the respective shelf, stand, room, floor, building, site and the number of volumes of each', function () {
    Box::factory()->create(['number' => 10]);
    Box::factory()->has(BoxVolume::factory(1), 'volumes')->create(['number' => 20]);
    Box::factory()->has(BoxVolume::factory(2), 'volumes')->create(['number' => 30]);

    $all = Box::hierarchy()->get();

    $box_10 = $all->firstWhere('number', 10);
    $box_20 = $all->firstWhere('number', 20);
    $box_30 = $all->firstWhere('number', 30);

    expect($all)->toHaveCount(3)
    ->and(empty($box_10->site_name))->toBeFalse()
    ->and(empty($box_10->building_name))->toBeFalse()
    ->and(empty($box_10->floor_number))->toBeFalse()
    ->and(empty($box_10->room_number))->toBeFalse()
    ->and(empty($box_10->stand_number))->toBeFalse()
    ->and(empty($box_10->shelf_number))->toBeFalse()
    ->and($box_10->volumes_count)->toBe(0)
    ->and(empty($box_20->site_name))->toBeFalse()
    ->and(empty($box_20->building_name))->toBeFalse()
    ->and(empty($box_20->floor_number))->toBeFalse()
    ->and(empty($box_20->room_number))->toBeFalse()
    ->and(empty($box_20->stand_number))->toBeFalse()
    ->and(empty($box_20->shelf_number))->toBeFalse()
    ->and($box_20->volumes_count)->toBe(1)
    ->and(empty($box_30->site_name))->toBeFalse()
    ->and(empty($box_30->building_name))->toBeFalse()
    ->and(empty($box_30->floor_number))->toBeFalse()
    ->and(empty($box_30->room_number))->toBeFalse()
    ->and(empty($box_30->stand_number))->toBeFalse()
    ->and(empty($box_30->shelf_number))->toBeFalse()
    ->and($box_30->volumes_count)->toBe(2);
});
