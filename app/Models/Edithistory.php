<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edithistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_type',
        'edit_type',
        'item_id',
        'category_id',
        'edited_field',
        'old_value',
        'new_value',
        'edit_user',
        'edited_at'
    ];

    public function scopeBetweenDate($query, $startDate = null, $endDate = null)
    {
        if(is_null($startDate) && is_null($endDate))
        { return $query; }

        if(!is_null($startDate) && is_null($endDate))
        { return $query->where('created_at', ">=", $startDate); }

        if(is_null($startDate) && !is_null($endDate))
        {
            return $query->where('created_at', '<=', $endDate);
        }

        if(!is_null($startDate) && !is_null($endDate))
        {
            return $query->where('created_at', ">=", $startDate)
            ->where('created_at', '<=', $endDate);
        }
    }
}
