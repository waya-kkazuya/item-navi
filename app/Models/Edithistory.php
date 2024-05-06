<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edithistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'edited_field',
        'old_value',
        'new_value',
        'edit_user',
        'edited_at'
    ];
}
