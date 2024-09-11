<?php

namespace Database\Factories;

use App\Models\AcquisitionMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\EditReason;
use App\Models\Unit;
use App\Models\Location;
use App\Models\UsageStatus;
use Illuminate\Support\Facades\Schema;

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
        // $user = ['管理者', 'スタッフ', '利用者'];
        // $usageStatus = ['未使用', '使用中'];


        $operation_types = ['store', 'update', 'stock_in', 'stock_out', 'delete', 'restore'];
        $operation_type = $this->faker->randomElement($operation_types);

        $editedFields = $this->getEditableFields();
        $editedField = $this->faker->randomElement($editedFields);

        // [$oldValue, $newValue] = match ($editedField) {
        //     'name' => [$this->faker->name(), $this->faker->name()],
        //     'category_id' => [Category::factory(), Category::factory()],
        //     'stock' => [$this->faker->numberBetween(1, 50), $this->faker->numberBetween(1, 50)],
        //     'unit_id' => [Unit::factory(), Unit::factory()],
        //     'minimum_stock' => [$this->faker->numberBetween(0, 5), $this->faker->numberBetween(0, 5)],
        //     'notification' => [$this->faker->boolean(), $this->faker->boolean()],
        //     'usage_status_id' => [UsageStatus::factory(), UsageStatus::factory()],
        //     'end_user' => [$this->faker->name(), $this->faker->name()],
        //     'location_of_use_id' => [Location::factory(), Location::factory()],
        //     'storage_location_id' => [Location::factory(), Location::factory()],
        //     'acquisition_method_id' => [AcquisitionMethod::factory(), AcquisitionMethod::factory()],
        //     'acquisition_source' => [$this->faker->name(), $this->faker->name()],
        //     'price' => [$this->faker->numberBetween(100, 100000), $this->faker->numberBetween(100, 100000)],
        //     'date_of_acquisition' => [$this->faker->dateTime(), $this->faker->dateTime()],
        //     'manufacturer' => [$this->faker->name(), $this->faker->name()],
        //     'product_number' => [$this->faker->name(), $this->faker->name()],
        //     'remarks' => [$this->faker->realText(20), $this->faker->realText(20)],
        //     default => [$this->faker->name, $this->faker->name],
        // };


        // 備品の情報の編集更新でstockを変更してもそれは情報の修正であって
        // 入出庫処理にはならない=>StockTransactionに入出庫処理情報は保存される

        // dd($editedField); //ちゃんと止まる

        return [
            'item_id' => Item::factory(),
            'edit_mode' => 'normal', // 棚卸し用にnormalとinventoryを想定
            'operation_type' => $operation_type,
            'edited_field'=> $editedField,
            'old_value' => 'after',
            'new_value'=> 'before',
            'edit_user' => User::factory(),
            'edit_reason_id' => EditReason::factory(),
            'edit_reason_text' => $this->faker->realText(50)
        ];
    }

    private function getEditableFields()
    {
        // 変更されるitemsテーブルのカラムを抽出
        $columns = Schema::getColumnListing('items');
        $excludedColumns = ['id', 'management_id', 'image1', 'qrcode','deleted_at', 'created_at', 'updated_at'];

        return array_diff($columns, $excludedColumns);
    }
}
