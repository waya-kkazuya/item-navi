<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'scheduled_date',
        'disposal_date',
        'disposal_person',
        'details',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }
}
