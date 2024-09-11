<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'location_of_use_id',
        'requestor',
        'remarks_from_requestor',
        'request_status_id',
        'manufacturer',
        'reference',
        'price',
    ];

    public function category() 
    {
        return $this->belongsTo(Category::class);
    }

    public function locationOfUse()
    {
        return $this->belongsTo(Location::class, 'location_of_use_id');
    }

    public function requestStatus()
    {
        return $this->belongsTo(RequestStatus::class);       
    }

}
