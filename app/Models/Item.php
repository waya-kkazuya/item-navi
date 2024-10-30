<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Location;
use App\Models\Unit;
use App\Models\Disposal;
use App\Models\InventoryPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'management_id',
        'name',
        'category_id',
        'image1',
        'stock',
        'unit_id',
        'minimum_stock',
        'notification',
        'usage_status_id',
        'end_user',
        'location_of_use_id',
        'storage_location_id',
        'acquisition_method_id',
        'acquisition_source',
        'price',
        'date_of_acquisition',
        'manufacturer',
        'product_number',
        'remarks',
        'qrcode',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);       
    }

    public function usageStatus()
    {
        return $this->belongsTo(UsageStatus::class);       
    }

    public function locationOfUse()
    {
        return $this->belongsTo(Location::class, 'location_of_use_id');
    }

    public function storageLocation()
    {
        return $this->belongsTo(Location::class, 'storage_location_id');
    }

    public function acquisitionMethod()
    {
        return $this->belongsTo(AcquisitionMethod::class);       
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function disposal()
    {
        return $this->hasOne(Disposal::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function scopeSearchItems($query, $input = null)
    {   
        if(!empty($input)){
            return $query->where('name', 'like', "%{$input}%");
        }
    }

    // createad_atを'Y-m-d'形式にするアクセサ
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
    
}
