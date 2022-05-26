<?php

/**
 * @see https://pestphp.com/docs/
 */
test('can read the corporate file', function () {
    $full_path = config('company.corporate_file');

    expect((new \SplFileInfo($full_path))->isReadable())->toBeTrue();
})->group('integration');
