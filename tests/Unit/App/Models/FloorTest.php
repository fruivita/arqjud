<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Floor;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create floors in duplicate, that is, with the same numbers and building', function () {
    $building = Building::factory()->create();

    expect(
        fn () => Floor::factory(2)->create([
            'number' => 100,
            'building_id' => $building->id
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create floor with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Floor::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number',      -2147483649,      'Out of range'],             // min -2147483648
    ['number',      2147483648,       'Out of range'],             // max 2147483647
    ['number',      null,             'cannot be null'],           // required
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Floor::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['building_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['building_id', null, 'cannot be null'],                   // nonexistent
]);

// Happy path
test('create many floors', function () {
    Floor::factory(30)->create();

    expect(Floor::count())->toBe(30);
});

test('fields in their minimum size are accepted', function () {
    Floor::factory()->create(['number' => -2147483648]);

    expect(Floor::count())->toBe(1);
});

test('fields in their maximum size are accepted', function () {
    Floor::factory()->create([
        'number' => 2147483647,
        'description' => Str::random(255),
    ]);

    expect(Floor::count())->toBe(1);
});

test('zero is a valid value for the floor number.', function () {
    Floor::factory()->create(['number' => 0]);

    expect(Floor::count())->toBe(1);
});

test('optional fields are set', function () {
    Floor::factory()->create(['description' => null]);

    expect(Floor::count())->toBe(1);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $floor_1 = Floor::factory()->create(['number' => 100]);
    $floor_2 = Floor::factory()->create(['number' => 200]);

    expect($floor_2->previous()->first()->id)->toBe($floor_1->id)
    ->and($floor_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $floor_1 = Floor::factory()->create(['number' => 100]);
    $floor_2 = Floor::factory()->create(['number' => 200]);

    expect($floor_1->next()->first()->id)->toBe($floor_2->id)
    ->and($floor_2->next()->first())->toBeNull();
});

test('returns the floors using the default sort scope defined', function () {
    $first = 100;
    $second = 200;
    $third = 300;

    Floor::factory()->create(['number' => $third]);
    Floor::factory()->create(['number' => $first]);
    Floor::factory()->create(['number' => $second]);

    $Floors = Floor::defaultOrder()->get();

    expect($Floors->get(0)->number)->toBe($first)
    ->and($Floors->get(1)->number)->toBe($second)
    ->and($Floors->get(2)->number)->toBe($third);
});

test('one floor belongs to one building', function () {
    $building = Building::factory()->create();

    $floor = Floor::factory()
        ->for($building, 'building')
        ->create();

    $floor->load(['building']);

    expect($floor->building)->toBeInstanceOf(Building::class);
});

test('one floor has many rooms', function () {
    Floor::factory()
        ->has(Room::factory(3), 'rooms')
        ->create();

    $floor = Floor::with('rooms')->first();

    expect($floor->rooms)->toHaveCount(3);
});
