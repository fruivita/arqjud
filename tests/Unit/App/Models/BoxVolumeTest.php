<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Shelf;
use App\Models\Stand;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create box volumes in duplicate, that is, with the same number/alias and box', function () {
    $box = Box::factory()->create();

    expect(
        fn () => BoxVolume::factory(2)->create([
            'number' => 10,
            'box_id' => $box->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => BoxVolume::factory(2)->create([
            'alias' => 10,
            'box_id' => $box->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create box volume with invalid field', function ($field, $value, $message) {
    expect(
        fn () => BoxVolume::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number', 'foo',           'Incorrect integer value'],  // not convertible to integer
    ['number', -1,              'Out of range value'],       // min 0
    ['number', 4294967296,      'Out of range value'],       // max 4294967295
    ['alias', Str::random(101), 'Data too long for column'], // maximum 100 characters
    ['alias', null,             'cannot be null'],           // required
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => BoxVolume::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['box_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['box_id', null, 'cannot be null'],                   // required
]);

// Happy path
test('create many box volumes', function () {
    BoxVolume::factory(30)->create();

    expect(BoxVolume::count())->toBe(30);
});

test('fields in their maximum size are accepted', function () {
    BoxVolume::factory()->create([
        'number' => 4294967295,
        'alias' => Str::random(100),
    ]);

    expect(BoxVolume::count())->toBe(1);
});

test('one box volume belongs to one box', function () {
    $box = Box::factory()->create();

    $box_volume = BoxVolume::factory()
        ->for($box, 'box')
        ->create();

    $box_volume->load(['box']);

    expect($box_volume->box)->toBeInstanceOf(Box::class);
});

test('hierarchy returns all box volumes with the respective box, shelf, stand, room, floor, building, site id and number/name of each', function () {
    BoxVolume::factory(3)->create();

    $all = BoxVolume::hierarchy()->get();

    $box_volume = $all->random();

    expect($all)->toHaveCount(3)
    ->and(empty($box_volume->site_id))->toBeFalse()
    ->and(empty($box_volume->site_name))->toBeFalse()
    ->and(empty($box_volume->building_id))->toBeFalse()
    ->and(empty($box_volume->building_name))->toBeFalse()
    ->and(empty($box_volume->floor_id))->toBeFalse()
    ->and(empty($box_volume->floor_alias))->toBeFalse()
    ->and(empty($box_volume->floor_number))->toBeFalse()
    ->and(empty($box_volume->room_id))->toBeFalse()
    ->and(empty($box_volume->room_number))->toBeFalse()
    ->and(empty($box_volume->stand_id))->toBeFalse()
    ->and(empty($box_volume->stand_number))->toBeFalse()
    ->and(empty($box_volume->shelf_id))->toBeFalse()
    ->and(empty($box_volume->shelf_number))->toBeFalse()
    ->and(empty($box_volume->box_id))->toBeFalse()
    ->and(empty($box_volume->box_number))->toBeFalse()
    ->and(empty($box_volume->box_year))->toBeFalse();
});

test('forHumans returns data in human-readable format', function () {
    $stand = Stand::factory()->create(['number' => 10]);
    $shelf = Shelf::factory()->for($stand, 'stand')->create(['number' => 100]);
    $box = Box::factory()->for($shelf, 'shelf')->create(['number' => 1000, 'year' => 2020]);
    BoxVolume::factory()->for($box, 'box')->create(['number' => 50]);

    $box_volume = BoxVolume::hierarchy()->first();

    expect($box_volume->for_humans)->toBe('Vol. 50')
    ->and($box_volume->stand_for_humans)->toBe(10)
    ->and($box_volume->shelf_for_humans)->toBe(100)
    ->and($box_volume->box_for_humans)->toBe('1000/2020');
});

test('forHumans returns "Uninformed" if the stand or shelf number is zero', function () {
    $stand = Stand::factory()->create(['number' => 0]);
    $shelf = Shelf::factory()->for($stand, 'stand')->create(['number' => 0]);
    $box = Box::factory()->for($shelf, 'shelf')->create();
    BoxVolume::factory()->for($box, 'box')->create();

    $box_volume = BoxVolume::hierarchy()->first();

    expect($box_volume->stand_for_humans)->toBe(__('Uninformed'))
    ->and($box_volume->shelf_for_humans)->toBe(__('Uninformed'));
});
