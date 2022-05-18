<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Building;
use App\Models\Floor;
use App\Models\Site;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create buildings in duplicate, that is, with the same names and site', function () {
    $site = Site::factory()->create();

    expect(
        fn () => Building::factory(2)->create([
            'name' => 'foo',
            'site_id' => $site->id
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create building with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Building::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(101), 'Data too long for column'], // maximum 100 characters
    ['name', null,             'cannot be null'],           // required
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Building::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['site_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['site_id', null, 'cannot be null'],                   // nonexistent
]);

// Happy path
test('create many buildings', function () {
    Building::factory(30)->create();

    expect(Building::count())->toBe(30);
});

test('building name at its maximum size is accepted', function () {
    Building::factory()->create(['name' => Str::random(100)]);

    expect(Building::count())->toBe(1);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $building_1 = Building::factory()->create(['name' => 'bar']);
    $building_2 = Building::factory()->create(['name' => 'foo']);

    expect($building_2->previous()->first()->id)->toBe($building_1->id)
    ->and($building_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $building_1 = Building::factory()->create(['name' => 'bar']);
    $building_2 = Building::factory()->create(['name' => 'foo']);

    expect($building_1->next()->first()->id)->toBe($building_2->id)
    ->and($building_2->next()->first())->toBeNull();
});

test('returns the buildings using the default sort scope defined', function () {
    $first = 'bar';
    $second = 'baz';
    $third = 'foo';

    Building::factory()->create(['name' => $third]);
    Building::factory()->create(['name' => $first]);
    Building::factory()->create(['name' => $second]);

    $Buildings = Building::defaultOrder()->get();

    expect($Buildings->get(0)->name)->toBe($first)
    ->and($Buildings->get(1)->name)->toBe($second)
    ->and($Buildings->get(2)->name)->toBe($third);
});

test('one building belongs to one site', function () {
    $site = Site::factory()->create();

    $building = Building::factory()
        ->for($site, 'site')
        ->create();

    $building->load(['site']);

    expect($building->site)->toBeInstanceOf(Site::class);
});

test('one building has many floors', function () {
    Building::factory()
        ->has(Floor::factory(3), 'floors')
        ->create();

    $building = Building::with('floors')->first();

    expect($building->floors)->toHaveCount(3);
});
