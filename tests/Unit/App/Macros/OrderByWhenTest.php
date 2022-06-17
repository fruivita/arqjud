<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Site;

use function Spatie\PestPluginTestTime\testTime;

// Happy path
test('sort ascending', function () {
    Site::factory()->create(['name' => 'foo']);
    Site::factory()->create(['name' => 'bar']);
    Site::factory()->create(['name' => 'baz']);

    $ordered = Site::orderByWhen('name', 'asc')->get();

    $first = $ordered->get(0);
    $second = $ordered->get(1);
    $third = $ordered->get(2);

    expect($ordered)->toHaveCount(3)
    ->and($first->name)->toBe('bar')
    ->and($second->name)->toBe('baz')
    ->and($third->name)->toBe('foo');
});

test('sort descending', function () {
    Site::factory()->create(['name' => 'foo']);
    Site::factory()->create(['name' => 'baz']);
    Site::factory()->create(['name' => 'bar']);

    $ordered = Site::orderByWhen('name', 'desc')->get();

    $first = $ordered->get(0);
    $second = $ordered->get(1);
    $third = $ordered->get(2);

    expect($ordered)->toHaveCount(3)
    ->and($first->name)->toBe('foo')
    ->and($second->name)->toBe('baz')
    ->and($third->name)->toBe('bar');
});

test('if the column is not provided, use the default sorting, that is, sort by creation date from the most recent to the oldest', function () {
    testTime()->freeze();
    Site::factory()->create(['name' => 'foo']);

    testTime()->addMinute();
    Site::factory()->create(['name' => 'bar']);

    testTime()->addMinute();
    Site::factory()->create(['name' => 'baz']);

    $ordered = Site::orderByWhen('', 'asc')->get();

    $first = $ordered->get(0);
    $second = $ordered->get(1);
    $third = $ordered->get(2);

    expect($ordered)->toHaveCount(3)
    ->and($first->name)->toBe('baz')
    ->and($second->name)->toBe('bar')
    ->and($third->name)->toBe('foo');
});
