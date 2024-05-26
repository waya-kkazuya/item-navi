<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;

class InventoryPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class)
        ->withPivot('item_id', 'inventory_date', 'inventory_person', 'insuffcient_data_status', 'insuffcient_data_details', 'unknown_assets_status', 'unknown_assets_details', 'inventory_status');
    }
}
