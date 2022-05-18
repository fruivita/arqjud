<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Room;
use App\Models\Floor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create rooms in duplicate, that is, with the same numbers and floor', function () {
    $floor = Floor::factory()->create();

    expect(
        fn () => Room::factory(2)->create([
            'number' => '1st',
            'floor_id' => $floor->id
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create room with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Room::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number', Str::random(101), 'Data too long for column'], // maximum 100 characters
    ['number', null,             'cannot be null'],           // required
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Room::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['floor_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['floor_id', null, 'cannot be null'],                   // nonexistent
]);

// Happy path
test('create many rooms', function () {
    Room::factory(30)->create();

    expect(Room::count())->toBe(30);
});

test('room number at its maximum size is accepted', function () {
    Room::factory()->create(['number' => Str::random(100)]);

    expect(Room::count())->toBe(1);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $room_1 = Room::factory()->create(['number' => '100-A']);
    $room_2 = Room::factory()->create(['number' => '100-B']);

    expect($room_2->previous()->first()->id)->toBe($room_1->id)
    ->and($room_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $room_1 = Room::factory()->create(['number' => '100-A']);
    $room_2 = Room::factory()->create(['number' => '100-B']);

    expect($room_1->next()->first()->id)->toBe($room_2->id)
    ->and($room_2->next()->first())->toBeNull();
});

test('returns the rooms using the default sort scope defined', function () {
    $first = '100';
    $second = '200';
    $third = '300';

    Room::factory()->create(['number' => $third]);
    Room::factory()->create(['number' => $first]);
    Room::factory()->create(['number' => $second]);

    $Rooms = Room::defaultOrder()->get();

    expect($Rooms->get(0)->number)->toBe($first)
    ->and($Rooms->get(1)->number)->toBe($second)
    ->and($Rooms->get(2)->number)->toBe($third);
});

test('one room belongs to one floor', function () {
    $floor = Floor::factory()->create();

    $room = Room::factory()
        ->for($floor, 'floor')
        ->create();

    $room->load(['floor']);

    expect($room->floor)->toBeInstanceOf(Floor::class);
});
