<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InventoryPlan;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'image_path1',
        'image_path2',
        'image_path3',
        'stocks',
        'usage_status',
        'end_user',
        'location_of_use',
        'storage_location',
        'acquisition_category',
        'where_to_buy',
        'price',
        'date_of_acquisition',
        'inspection_schedule',
        'disposal_schedule',
        'manufacturer',
        'product_number',
        'remarks',
        'qrcode_path',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function inventory_plans()
    {
        return $this->belongsToMany(InventoryPlan::class)
        ->withPivot('item_id', 'inventory_date', 'inventory_person', 'insuffcient_data_status', 'insuffcient_data_details', 'unknown_assets_status', 'unknown_assets_details', 'inventory_status');
    }


    public function scopeSearchItems($query, $input = null)
    {
        if(!empty($input)){
            if(Item::where('name', 'like', "%{$input}%")->exists())
            {
                // return $query->where('name', 'like', $input . '%' );
                return $query->where('name', 'like', "%{$input}%");
            }
        }
    }

    
}
