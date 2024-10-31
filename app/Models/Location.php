<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;

class Location extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->hasOne(Item::class);
    }

    public function itemRequest()
    {
        return $this->hasOne(Wish::class);
    }
}
