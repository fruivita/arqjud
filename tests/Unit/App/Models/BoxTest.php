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
test('throws exception when trying to create boxs in duplicate, that is, with the same numbers and year', function () {
    expect(
        fn () => Box::factory(2)->create([
            'number' => '100-foo',
            'year' => '2020'
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create box with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Box::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number', Str::random(101), 'Data too long for column'], // maximum 100 characters
    ['number', null,             'cannot be null'],           // required
    ['year',  'foo',             'Incorrect integer value'],  // not convertible to integer
    ['year',  -1,                'Out of range value'],       // integer greater than zero
    ['stand', Str::random(101),  'Data too long for column'], // maximum 100 characters
    ['shelf', Str::random(101),  'Data too long for column'], // maximum 100 characters
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
test('create many boxs', function () {
    Box::factory(30)->create();

    expect(Box::count())->toBe(30);
});

test('box number at its maximum size is accepted', function () {
    Box::factory()->create(['number' => Str::random(100)]);

    expect(Box::count())->toBe(1);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $box_1 = Box::factory()->create(['number' => '100-bar']);
    $box_2 = Box::factory()->create(['number' => '100-foo']);

    expect($box_2->previous()->first()->id)->toBe($box_1->id)
    ->and($box_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $box_1 = Box::factory()->create(['number' => '100-bar']);
    $box_2 = Box::factory()->create(['number' => '100-foo']);

    expect($box_1->next()->first()->id)->toBe($box_2->id)
    ->and($box_2->next()->first())->toBeNull();
});

test('returns the boxs using the default sort scope defined', function () {
    $first = '100-foo';
    $second = '200-bar';
    $third = '300-baz';

    Box::factory()->create(['number' => $third]);
    Box::factory()->create(['number' => $first]);
    Box::factory()->create(['number' => $second]);

    $Boxs = Box::defaultOrder()->get();

    expect($Boxs->get(0)->number)->toBe($first)
    ->and($Boxs->get(1)->number)->toBe($second)
    ->and($Boxs->get(2)->number)->toBe($third);
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
