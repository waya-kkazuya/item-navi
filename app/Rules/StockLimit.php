<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockLimit implements ValidationRule
{
    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->item || $value > $this->item->stock) {
            $fail('在庫数以上の数量は出庫できません');
        }
    }
}
