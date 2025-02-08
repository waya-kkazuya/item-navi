<?php

namespace Database\Factories;

use App\Models\EditReason;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
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
        $operation_types = ['store', 'update', 'soft_delete', 'restore'];
        $operation_type  = $this->faker->randomElement($operation_types);

        $editedFields = $this->getEditableFields();
        $editedField  = $this->faker->randomElement($editedFields);

        return [
            'item_id'          => Item::factory(),
            'edit_mode'        => 'normal', // 棚卸し用にnormalとinventoryを想定
            'operation_type'   => $operation_type,
            'edited_field'     => $editedField,
            'old_value'        => 'after',
            'new_value'        => 'before',
            'edit_user'        => User::factory(),
            'edit_reason_id'   => EditReason::factory(),
            'edit_reason_text' => $this->faker->realText(50),
        ];
    }

    private function getEditableFields()
    {
        // 変更されるitemsテーブルのカラムを抽出
        $columns         = Schema::getColumnListing('items');
        $excludedColumns = ['id', 'management_id', 'image1', 'qrcode', 'deleted_at', 'created_at', 'updated_at'];

        return array_diff($columns, $excludedColumns);
    }
}
