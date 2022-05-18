<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\BoxVolume;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create box volumes in duplicate, that is, with the same number and box', function () {
    $box = Box::factory()->create();

    expect(
        fn () => BoxVolume::factory(2)->create([
            'number' => 10,
            'box_id' => $box->id
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create box volume with invalid field', function ($field, $value, $message) {
    expect(
        fn () => BoxVolume::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number', 'foo', 'Incorrect integer value'],  // not convertible to integer
    ['number', -1,    'Out of range value'],       // integer greater than zero
    ['number', 65536, 'Out of range value'],       // integer greater than zero
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => BoxVolume::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['box_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['box_id', null, 'cannot be null'],                   // nonexistent
]);

// Happy path
test('create many box volumes', function () {
    BoxVolume::factory(30)->create();

    expect(BoxVolume::count())->toBe(30);
});

test('box volume number at its maximum size is accepted', function () {
    BoxVolume::factory()->create(['number' => 65535]);

    expect(BoxVolume::count())->toBe(1);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $box_volume_1 = BoxVolume::factory()->create(['number' => 10]);
    $box_volume_2 = BoxVolume::factory()->create(['number' => 20]);

    expect($box_volume_2->previous()->first()->id)->toBe($box_volume_1->id)
    ->and($box_volume_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $box_volume_1 = BoxVolume::factory()->create(['number' => 10]);
    $box_volume_2 = BoxVolume::factory()->create(['number' => 20]);

    expect($box_volume_1->next()->first()->id)->toBe($box_volume_2->id)
    ->and($box_volume_2->next()->first())->toBeNull();
});

test('returns the box volumes using the default sort scope defined', function () {
    $first = 100;
    $second = 200;
    $third = 300;

    BoxVolume::factory()->create(['number' => $third]);
    BoxVolume::factory()->create(['number' => $first]);
    BoxVolume::factory()->create(['number' => $second]);

    $box_volumes = BoxVolume::defaultOrder()->get();

    expect($box_volumes->get(0)->number)->toBe($first)
    ->and($box_volumes->get(1)->number)->toBe($second)
    ->and($box_volumes->get(2)->number)->toBe($third);
});

test('one box volume belongs to one box', function () {
    $box = Box::factory()->create();

    $box_volume = BoxVolume::factory()
        ->for($box, 'box')
        ->create();

    $box_volume->load(['box']);

    expect($box_volume->box)->toBeInstanceOf(Box::class);
});
