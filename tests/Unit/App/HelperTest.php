<?php

/**
 * @see https://pestphp.com/docs/
 */

// Invalid
test('stringToArrayAssoc returns null if invalid values are given', function ($keys, $delimiter, $string) {
    expect(stringToArrayAssoc($keys, $delimiter, $string))->toBeNull();
})->with([
    [
        ['name', 'age', 'nationality', 'excess_key'], // qty of keys incompatible with string
        ',',
        'foo,18,bar',
    ],
    [
        [], // unspecified keys (empty array)
        ',',
        'foo,18,bar',
    ],
    [
        ['name', 'age', 'nationality'],
        ',',
        '', // unreported string (false boolean)
    ],
    [
        ['name', 'age', 'nationality'],
        '', // delimiter not informed (false boolean)
        'foo,18,bar',
    ],
]);

// Happy path
test('maxSafeInteger returns the value of the largest safe integer, i.e. not subject to truncation, for javascript work', function () {
    expect(maxSafeInteger())->toBe(9007199254740991);
});

test('stringToArrayAssoc explodes the string based on the delimiter and returns an associative array', function () {
    $keys = ['name', 'age', 'nationality'];
    $string = 'foo,18,bar';
    $delimiter = ',';
    $expected = [
        'name' => 'foo',
        'age' => '18',
        'nationality' => 'bar',
    ];

    expect(stringToArrayAssoc($keys, $delimiter, $string))->toMatchArray($expected);
});
