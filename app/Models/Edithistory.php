<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


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

    /**
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function editReason(): BelongsTo
    {
        return $this->belongsTo(EditReason::class);
    }
}
