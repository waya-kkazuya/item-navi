<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockLimit implements ValidationRule
{
    protected $itemId;

    public function __construct($itemId)
    {
        $this->itemId = $itemId;
    }

    public function passes($attribute, $value)
    {
        $item = Item::find($this->itemId);
        return $item && $value <= $item->stock;
    }

    public function message()
    {
        return 'The quantity must not exceed the available stock.';
    }


    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
    }
}
