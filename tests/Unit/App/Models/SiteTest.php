<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Building;
use App\Models\Site;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create sites in duplicate, that is, with the same names', function () {
    expect(
        fn () => Site::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create site with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Site::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name',        Str::random(101), 'Data too long for column'], // maximum 100 characters
    ['name',        null,             'cannot be null'],           // required
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

// Happy path
test('create many sites', function () {
    Site::factory(30)->create();

    expect(Site::count())->toBe(30);
});

test('fields in their maximum size are accepted', function () {
    Site::factory()->create([
        'name' => Str::random(100),
        'description' => Str::random(255),
    ]);

    expect(Site::count())->toBe(1);
});

test('optional fields are set', function () {
    Site::factory()->create(['description' => null]);

    expect(Site::count())->toBe(1);
});

test('returns the sites using the default sort scope defined', function () {
    $first = 'bar';
    $second = 'baz';
    $third = 'foo';

    Site::factory()->create(['name' => $third]);
    Site::factory()->create(['name' => $first]);
    Site::factory()->create(['name' => $second]);

    $sites = Site::defaultOrder()->get();

    expect($sites->get(0)->name)->toBe($first)
    ->and($sites->get(1)->name)->toBe($second)
    ->and($sites->get(2)->name)->toBe($third);
});

test('one site has many buildings', function () {
    Site::factory()
        ->has(Building::factory(3), 'buildings')
        ->create();

    $site = Site::with('buildings')->first();

    expect($site->buildings)->toHaveCount(3);
});


test('parentEntitiesLinks returns only show parents routes sorted from most distant to closest relationship if root is false', function () {
    $site = Site::factory()->create();

    expect($site->parentEntitiesLinks(false))->toBeEmpty();
});

test('parentEntitiesLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    $site = Site::factory()->create();

    expect($site->parentEntitiesLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $site),
    ]);
});
