<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use App\Models\Wish;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function item() {
        return $this->hasOne(Item::class);
    }

    public function itemRequest() 
    {
        return $this->hasOne(Item::class);
    }
}
