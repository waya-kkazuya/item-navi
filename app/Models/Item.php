<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'price',
        'date_of_acquisition',
        'inspection_schedule',
        'disposal_schedule',
        'manufacturer',
        'product_number',
        'vendor',
        'vendor_website_url',
        'remarks',
        'qrcode_path',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
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
