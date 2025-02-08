<?php

namespace Database\Factories;

use App\Models\AcquisitionMethod;
use App\Models\Category;
use App\Models\Location;
use App\Models\Unit;
use App\Models\UsageStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'management_id'         => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'name'                  => $this->faker->name(),
            'category_id'           => Category::factory(),
            'image1'                => $this->faker->regexify('[A-Za-z0-9]{20}'),
            'stock'                 => $this->faker->numberBetween(1, 20),
            'unit_id'               => Unit::factory(),
            'minimum_stock'         => $this->faker->numberBetween(1, 5),
            'notification'          => $this->faker->boolean(),
            'usage_status_id'       => UsageStatus::factory(),
            'end_user'              => $this->faker->name(),
            'location_of_use_id'    => Location::factory(),
            'storage_location_id'   => Location::factory(),
            'acquisition_method_id' => AcquisitionMethod::factory(),
            'acquisition_source'    => $this->faker->name(),
            'price'                 => $this->faker->numberBetween(100, 50000),
            'date_of_acquisition'   => $this->faker->dateTime(),
            'manufacturer'          => $this->faker->sentence(2),
            'product_number'        => $this->faker->regexify('[A-Za-z0-9]{10,20}'),
            'remarks'               => $this->faker->realText(100),
            'qrcode'                => $this->faker->regexify('[A-Za-z0-9]{20}'),
        ];
    }
}
