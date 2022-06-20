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
            'site_id' => $site->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create building with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Building::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name',        Str::random(101), 'Data too long for column'], // maximum 100 characters
    ['name',        null,             'cannot be null'],           // required
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Building::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['site_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['site_id', null, 'cannot be null'],                   // required
]);

// Happy path
test('create many buildings', function () {
    Building::factory(30)->create();

    expect(Building::count())->toBe(30);
});

test('fields in their maximum size are accepted', function () {
    Building::factory()->create([
        'name' => Str::random(100),
        'description' => Str::random(255),
    ]);

    expect(Building::count())->toBe(1);
});

test('optional fields are set', function () {
    Site::factory()->create(['description' => null]);

    expect(Site::count())->toBe(1);
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

test('parentLinks returns only show parents routes sorted from most distant to closest relationship if root is false', function () {
    Building::factory()->create();

    $building = Building::hierarchy()->first();

    expect($building->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $building->site_id),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    Building::factory()->create();

    $building = Building::hierarchy()->first();

    expect($building->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $building->site_id),
        __('Building') => route('archiving.register.building.show', $building->id),
    ]);
});

test('hierarchy returns all buildings with the respective site id and number/name and the number of floors of each', function () {
    Building::factory()->create(['name' => 'foo']);
    Building::factory()->has(Floor::factory(1), 'floors')->create(['name' => 'bar']);
    Building::factory()->has(Floor::factory(2), 'floors')->create(['name' => 'baz']);

    $all = Building::hierarchy()->get();

    $foo = $all->firstWhere('name', 'foo');
    $bar = $all->firstWhere('name', 'bar');
    $baz = $all->firstWhere('name', 'baz');

    expect($all)->toHaveCount(3)
    ->and(empty($foo->site_id))->toBeFalse()
    ->and(empty($foo->site_name))->toBeFalse()
    ->and($foo->floors_count)->toBe(0)
    ->and($bar->floors_count)->toBe(1)
    ->and($baz->floors_count)->toBe(2);
});

test('hierarchical data returns the data present in the model or searches the database', function () {
    $site = Site::factory()->create();
    Building::factory()
    ->for($site, 'site')
    ->has(Floor::factory(2), 'floors')
    ->create();

    $data = Building::first()->hierarchicalData();

    expect($data->get('site_id'))->toBe($site->id)
    ->and($data->get('site_name'))->toBe($site->name)
    ->and($data->get('floors_count'))->toBe(2);
});
