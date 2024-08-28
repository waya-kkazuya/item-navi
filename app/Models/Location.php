<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use App\Models\Wish;

class Location extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->hasOne(Item::class);
    }

    public function wish()
    {
        return $this->hasOne(Wish::class);
    }

    public function itemRequest()
    {
        return $this->hasOne(Wish::class);
    }
}
