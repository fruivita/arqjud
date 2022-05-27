<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Room;
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
    $box_1 = Box::factory()->create([
        'number' => 200,
        'year' => 2020,
    ]);
    $box_2 = Box::factory()->create([
        'number' => 100,
        'year' => 2020,
    ]);

    expect($box_2->previous()->first()->id)->toBe($box_1->id)
    ->and($box_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $box_1 = Box::factory()->create([
        'number' => 200,
        'year' => 2020,
    ]);
    $box_2 = Box::factory()->create([
        'number' => 100,
        'year' => 2020,
    ]);

    expect($box_1->next()->first()->id)->toBe($box_2->id)
    ->and($box_2->next()->first())->toBeNull();
});

test('previous returns the correct previous record, even if it is the first and the year is different', function () {
    $box_1 = Box::factory()->create([
        'number' => 200,
        'year' => 2021,
    ]);
    $box_2 = Box::factory()->create([
        'number' => 500,
        'year' => 2020,
    ]);
    $box_3 = Box::factory()->create([
        'number' => 100,
        'year' => 2020,
    ]);

    expect($box_3->previous()->first()->id)->toBe($box_2->id)
    ->and($box_2->previous()->first()->id)->toBe($box_1->id)
    ->and($box_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last and the year is different', function () {
    $box_1 = Box::factory()->create([
        'number' => 200,
        'year' => 2021,
    ]);
    $box_2 = Box::factory()->create([
        'number' => 500,
        'year' => 2020,
    ]);
    $box_3 = Box::factory()->create([
        'number' => 100,
        'year' => 2020,
    ]);

    expect($box_1->next()->first()->number)->toBe($box_2->number)
    ->and($box_2->next()->first()->number)->toBe($box_3->number)
    ->and($box_3->next()->first())->toBeNull();
});

test('name returns the name of the box ready for display', function () {
    $box = Box::factory()->create(['number' => 100, 'year' => 2020]);

    expect($box->name())->toBe('100/2020');
});

test('returns the boxes using the default sort scope defined', function () {
    $second = Box::factory()->create([
        'number' => 100,
        'year' => 2020,
    ]);
    $first = Box::factory()->create([
        'number' => 200,
        'year' => 2020,
    ]);
    $third = Box::factory()->create([
        'number' => 100,
        'year' => 2019,
    ]);

    $boxes = Box::defaultOrder()->get();

    expect($boxes->get(0)->id)->toBe($first->id)
    ->and($boxes->get(1)->id)->toBe($second->id)
    ->and($boxes->get(2)->id)->toBe($third->id);
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

test('createMany method creates and persists sequential boxes with equal attributes and sequential boxes', function () {
    $template = Box::factory()->makeOne(['number' => 10]);
    $room = Room::factory()->create();

    Box::createMany($template, 30, 5, $room);

    $boxes = Box::with('volumes')->get();

    $box = $boxes->random();

    expect($boxes)->toHaveCount(30)
    ->and($room->load('boxes')->boxes)->toHaveCount(30)
    ->and($boxes->first()->number)->toBe(10)
    ->and($boxes->last()->number)->toBe(39)
    ->and($box->year)->toBe($template->year)
    ->and($box->stand)->toBe($template->stand)
    ->and($box->shelf)->toBe($template->shelf)
    ->and($box->volumes)->toHaveCount(5)
    ->and($box->volumes->first()->number)->toBe(1)
    ->and($box->volumes->last()->number)->toBe(5);
});
