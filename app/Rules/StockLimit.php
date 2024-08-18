<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockLimit implements ValidationRule
{
    protected $itemId;

    public function __construct($item)
    {
        \Log::info('Item __construct:');
        \Log::info($item);
        $this->item = $item;
    }

    public function passes($attribute, $value)
    {
        \Log::info('Item:');
        \Log::info($this->item);
        \Log::info('Value:');
        \Log::info($value);

        return $this->item && $value <= $this->item->stock;
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
