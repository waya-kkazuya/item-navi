<?php

namespace App\UseCases\Dashboard;

use App\Models\Item;

class ItemsByTypeUseCase
{
    public function handle(string $type)
    {
        if ($type == 'category') {
            // 備品をカテゴリでまとめたもの
            $allItems    = Item::with('category')->get();
            $itemsByType = $allItems->groupBy(function ($item) {
                return $item->category->name;
            })->map(function ($group) {
                return [
                    'category_id' => $group->first()->category->id,
                    'items'       => $group,
                ];
            });
        } else {
            // 備品を利用場所でまとめたもの
            $allItems    = Item::with('locationOfUse')->get();
            $itemsByType = $allItems->groupBy(function ($item) {
                return $item->locationOfUse->name;
            })->map(function ($group) {
                return [
                    'location_of_use_id' => $group->first()->locationOfUse->id,
                    'items'              => $group,
                ];
            });
        }

        return [
            'allItems'    => $allItems,
            'itemsByType' => $itemsByType,
        ];
    }
}
