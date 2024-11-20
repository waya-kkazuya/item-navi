<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'transaction_type',
        'quantity',
        'operator_name',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
