<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'scheduled_date',
        'inspection_date',
        'status',
        'inspection_person',
        'details',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
