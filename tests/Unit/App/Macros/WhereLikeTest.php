<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\Site;

// Happy path
test('filters the records that partially contain the given term', function () {
    Site::factory()->create(['name' => 'foo']);
    Site::factory()->create(['name' => 'bar']);
    Site::factory()->create(['name' => 'baz']);

    $result = Site::whereLike('name', 'a')->orderBy('name', 'asc')->get();

    $bar = $result->get(0);
    $baz = $result->get(1);

    expect($result)->toHaveCount(2)
    ->and($bar->name)->toBe('bar')
    ->and($baz->name)->toBe('baz');
});

test('filters the records by several columns that partially contain the given term', function () {
    Box::factory()->create(['number' => 150, 'year' => 1999]);
    Box::factory()->create(['number' => 500, 'year' => 2021]);
    Box::factory()->create(['number' => 329, 'year' => 2021]);

    $result = Box::whereLike(['number', 'year'], 9)->orderBy('number', 'asc')->get();

    $box_150 = $result->get(0);
    $box_329 = $result->get(1);

    expect($result)->toHaveCount(2)
    ->and($box_150->number)->toBe(150)
    ->and($box_150->year)->toBe(1999)
    ->and($box_329->number)->toBe(329)
    ->and($box_329->year)->toBe(2021);
});

test('if the search term is not provided, the where clause is not applied', function () {
    $query = Site::whereLike('name', '')->toSql();

    expect($query)->toBe('select * from `sites`');
});
