<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Room;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create boxes in duplicate, that is, with the same numbers and year', function () {
    expect(
        fn () => Box::factory(2)->create([
            'number' => 100,
            'year' => 2020
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
    ['year',   -1,         'Out of range value'],       // integer greater than zero
    ['year',   65536,      'Out of range value'],       // integer greater than zero
    ['year',   'foo',      'Incorrect integer value'],  // not convertible to integer
    ['stand',  -1,         'Out of range'],             // min 0
    ['stand',  4294967296, 'Out of range'],             // max 4294967295
    ['stand',  'foo',      'Incorrect integer value'],  // not convertible to integer
    ['shelf',  -1,         'Out of range'],             // min 0
    ['shelf',  4294967296, 'Out of range'],             // max 4294967295
    ['shelf',  'foo',      'Incorrect integer value'],  // not convertible to integer
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Box::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['room_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['room_id', null, 'cannot be null'],                   // nonexistent
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
        'stand' => 4294967295,
        'shelf' => 4294967295,
    ]);

    expect(Box::count())->toBe(1);
});

test('optional fields are set', function () {
    Box::factory()->create([
        'stand' => null,
        'shelf' => null,
    ]);

    expect(Box::count())->toBe(1);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $box_1 = Box::factory()->create(['number' => 100]);
    $box_2 = Box::factory()->create(['number' => 200]);

    expect($box_2->previous()->first()->id)->toBe($box_1->id)
    ->and($box_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $box_1 = Box::factory()->create(['number' => 100]);
    $box_2 = Box::factory()->create(['number' => 200]);

    expect($box_1->next()->first()->id)->toBe($box_2->id)
    ->and($box_2->next()->first())->toBeNull();
});

test('name returns the name of the box ready for display', function () {
    $box = Box::factory()->create(['number' => 100, 'year' => 2020]);

    expect($box->name())->toBe('100/2020');
});

test('returns the boxes using the default sort scope defined', function () {
    $first = 100;
    $second = 200;
    $third = 300;

    Box::factory()->create(['number' => $third]);
    Box::factory()->create(['number' => $first]);
    Box::factory()->create(['number' => $second]);

    $boxes = Box::defaultOrder()->get();

    expect($boxes->get(0)->number)->toBe($first)
    ->and($boxes->get(1)->number)->toBe($second)
    ->and($boxes->get(2)->number)->toBe($third);
});

test('one box belongs to one room', function () {
    $room = Room::factory()->create();

    $box = Box::factory()
        ->for($room, 'room')
        ->create();

    $box->load(['room']);

    expect($box->room)->toBeInstanceOf(Room::class);
});

test('one box has many box volumes', function () {
    Box::factory()
        ->has(BoxVolume::factory(3), 'volumes')
        ->create();

    $box = Box::with('volumes')->first();

    expect($box->volumes)->toHaveCount(3);
});

test('search, with partial term or not, returns the expected values', function () {
    Box::factory()->create(['number' => 100, 'year' => 2015]);
    Box::factory()->create(['number' => 120152, 'year' => 2020]);
    Box::factory()->create(['number' => 200, 'year' => 2020]);

    expect(Box::search('20')->get())->toHaveCount(3)
    ->and(Box::search('2015')->get())->toHaveCount(2)
    ->and(Box::search('12015')->get())->toHaveCount(1)
    ->and(Box::search('10')->get())->toHaveCount(1)
    ->and(Box::search('100')->get())->toHaveCount(1);
});
