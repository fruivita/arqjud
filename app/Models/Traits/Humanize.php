<?php

namespace App\Models\Traits;

/**
 * Transforms attributes of a given object into a human-readable format.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 */
trait Humanize
{
    /**
     * Get the stand in human-readable format.
     *
     * @param int $number stand's number
     *
     * @return mixed
     */
    private function humanizeStand(int $number)
    {
        return $number ?: __('Uninformed');
    }

    /**
     * Get the shelf in human-readable format.
     *
     * @param int $number shelf's number
     *
     * @return string|int
     */
    private function humanizeShelf(int $number)
    {
        return $number ?: __('Uninformed');
    }

    /**
     * Get the box in human-readable format.
     *
     * @param int $number box's number
     * @param int $year   box's year
     *
     * @return string
     */
    private function humanizeBox(int $number, int $year)
    {
        return "{$number}/{$year}";
    }

    /**
     * Get the box volume in human-readable format.
     *
     * @param int $number box volume's number
     *
     * @return string
     */
    private function humanizeBoxVolume(int $number)
    {
        return "Vol. {$number}";
    }
}
