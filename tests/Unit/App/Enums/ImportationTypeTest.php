<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\ImportationType;

test('ImportationType enum correctly defined', function () {
    expect(ImportationType::Corporate->value)->toBe('corporate');
});

test('ImportationType enum values defined', function () {
    expect(ImportationType::values()->toArray())->toBe(['corporate']);
});

test('ImportationType enum label defined', function () {
    expect(ImportationType::Corporate->label())->toBe(__('Corporate structure'));
});

test('ImportationType enum queue defined', function () {
    expect(ImportationType::Corporate->queue())->toBe('corporate');
});
