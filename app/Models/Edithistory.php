<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Edithistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'edit_mode',
        'operation_type',
        'item_id',
        'edited_field',
        'old_value',
        'new_value',
        'edit_user',
        'edit_reason_id',
        'edit_reason_text'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function editReason()
    {
        return $this->belongsTo(EditReason::class);
    }
}
