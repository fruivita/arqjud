<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Building;
use App\Models\Site;

use function Spatie\PestPluginTestTime\testTime;

// Happy path
test('sort ascending', function () {
    Building::factory()->create(['name' => 'foo']);
    Building::factory()->create(['name' => 'bar']);
    Building::factory()->create(['name' => 'baz']);

    $ordered = Building::orderByWhen(['name' => 'asc'])->get();

    $first = $ordered->get(0);
    $second = $ordered->get(1);
    $third = $ordered->get(2);

    expect($ordered)->toHaveCount(3)
    ->and($first->name)->toBe('bar')
    ->and($second->name)->toBe('baz')
    ->and($third->name)->toBe('foo');
});

test('sort descending', function () {
    Building::factory()->create(['name' => 'foo']);
    Building::factory()->create(['name' => 'baz']);
    Building::factory()->create(['name' => 'bar']);

    $ordered = Building::orderByWhen(['name' => 'desc'])->get();

    $first = $ordered->get(0);
    $second = $ordered->get(1);
    $third = $ordered->get(2);

    expect($ordered)->toHaveCount(3)
    ->and($first->name)->toBe('foo')
    ->and($second->name)->toBe('baz')
    ->and($third->name)->toBe('bar');
});

test('if the sorting array is not provided, use the default sorting, that is, sort by creation date from the most recent to the oldest', function () {
    testTime()->freeze();
    Building::factory()->create(['name' => 'foo']);

    testTime()->addMinute();
    Building::factory()->create(['name' => 'bar']);

    testTime()->addMinute();
    Building::factory()->create(['name' => 'baz']);

    $ordered = Building::orderByWhen([])->get();

    $first = $ordered->get(0);
    $second = $ordered->get(1);
    $third = $ordered->get(2);

    expect($ordered)->toHaveCount(3)
    ->and($first->name)->toBe('baz')
    ->and($second->name)->toBe('bar')
    ->and($third->name)->toBe('foo');
});

test('na ordenação default, se a data de criação dos registros for a mesma, ordena pelo ID desc', function () {
    testTime()->freeze();
    Building::factory()->create(['id' => 2 , 'name' => 'bar']);
    Building::factory()->create(['id' => 3, 'name' => 'foo']);

    testTime()->addMinute();
    Building::factory()->create(['id' => 5, 'name' => 'baz']);

    $ordered = Building::orderByWhen([])->get();

    $first = $ordered->get(0);
    $second = $ordered->get(1);
    $third = $ordered->get(2);

    expect($ordered)->toHaveCount(3)
    ->and($first->name)->toBe('baz')
    ->and($second->name)->toBe('foo')
    ->and($third->name)->toBe('bar');
});

test('if the hierarchy is present, order by multiple multiple columns', function () {
    $site_foo = Site::factory()->create(['name' => 'foo']);
    $site_bar = Site::factory()->create(['name' => 'bar']);

    Building::factory()->for($site_foo, 'site')->create(['name' => 'loren']);
    Building::factory()->for($site_foo, 'site')->create(['name' => 'ipsun']);
    Building::factory()->for($site_foo, 'site')->create(['name' => 'dolor']);
    Building::factory()->for($site_bar, 'site')->create(['name' => 'tempor']);
    Building::factory()->for($site_bar, 'site')->create(['name' => 'labore']);

    $ordered = Building::hierarchy()->orderByWhen(['sites.name' => 'asc', 'buildings.name' => 'asc'])->get();

    $first = $ordered->get(0);
    $second = $ordered->get(1);
    $third = $ordered->get(2);
    $fourth = $ordered->get(3);
    $fifth = $ordered->get(4);

    expect($ordered)->toHaveCount(5)
    ->and($first->name)->toBe('labore')
    ->and($second->name)->toBe('tempor')
    ->and($third->name)->toBe('dolor')
    ->and($fourth->name)->toBe('ipsun')
    ->and($fifth->name)->toBe('loren');
});
