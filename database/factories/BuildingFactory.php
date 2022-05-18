<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Building>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class BuildingFactory extends Factory
{
    protected $model = Building::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->streetName(),
            'site_id' => Site::factory(),
        ];
    }
}
