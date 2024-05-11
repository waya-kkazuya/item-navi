<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Edithistory>
 */
class EdithistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $location = ['作業室1', '作業室2', '玄関', '廊下', '給湯室', 'トイレ', '事務室'];
        $user = ['管理者', 'スタッフ', '利用者'];
        $usageStatus = ['未使用', '使用中'];
        $itemColumn = ['name', 'category_id', 'image_path1', 'image_path2', 'image_path1',
                'stocks', 'usage_status', 'end_user', 'location_of_use', 'storage_location',
                'acquisition_category', 'price', 'date_of_acquisition', 'inspection_schedule',
                'disposal_schedule', 'manufacturer', 'product_number', 'vendor', 'vendor_website_url',
                'remarks'];

        $categoryId = Category::all()->random()->id;
        $editedField = $this->faker->randomElement($itemColumn);
        
        if($editedField === 'stocks' && $categoryId == 1 ) {
            $oldValue = $this->faker->numberBetween(1, 500);
            $newValue = $this->faker->numberBetween(1, 500);
        } elseif($editedField === 'price') {
            $oldValue = $this->faker->numberBetween(100, 50000);
            $newValue = $this->faker->numberBetween(100, 50000);        
        } elseif($editedField === 'date_of_acquisition' || $editedField === 'inspection_schedule' || $editedField === 'disposal_schedule') {
            $oldValue = $this->faker->dateTime;
            $newValue = $this->faker->dateTime;
        } elseif($editedField === 'location_of_use'|| $editedField === 'storage_location') {
            $oldValue = $this->faker->randomElement($location);
            $newValue = $this->faker->randomElement($location);
        } elseif($editedField === 'product_number') {
            $oldValue = $this->faker->numberBetween(1000, 100000);
            $newValue = $this->faker->numberBetween(1000, 100000);
        } elseif($editedField === 'vendor_website_url') {
            $oldValue = $this->faker->url;
            $newValue = $this->faker->url;
        } elseif($editedField === 'remarks') {
            $oldValue = $this->faker->realText(20);
            $newValue = $this->faker->realText(20);
        } elseif($editedField === 'usage_status') {
            $oldValue = $this->faker->randomElement($usageStatus);
            $newValue = $this->faker->randomElement($usageStatus);
        }  else {
            $oldValue = $this->faker->name;
            $newValue = $this->faker->name;
        }

        return [
            'item_id' => Item::all()->random()->id,
            'category_id' => $categoryId,
            'edited_field'=> $editedField,
            'old_value' => $oldValue,
            'new_value'=> $newValue,
            'edit_user' => $this->faker->randomElement($user),
            'edited_at' => $this->faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')->format('Y-m-d H:i:s')
        ];
    }
}
