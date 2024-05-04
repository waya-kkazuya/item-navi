<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function item() {
        return $this->hasOne(Item::class);
    }

    public function wish() {
        return $this->hasOne(Wish::class);
    }
}
