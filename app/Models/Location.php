<?php

namespace App\Models;

use App\Models\Item;
use App\Models\ItemRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Location extends Model
{
    use HasFactory;

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(Item::class);
    }

    /**
     * @return HasOne
     */
    public function itemRequest(): HasOne
    {
        return $this->hasOne(ItemRequest::class);
    }
}
