<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\StockLimit;
use Illuminate\Support\Facades\Log;

class UpdateStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $item = $this->route('item'); // ルートパラメータからアイテムIDを取得

        Log::info($item);

        return [
            // 'item_id' => ['required', 'exists:items,id'],
            'transactionType' => ['required', 'in:入庫,出庫'],
            'transactionDate' => ['required', 'date'], // 1ヵ月前まで
            'operatorName' => ['required', 'max:10'] ,
            'quantity' => ['required', 'integer',  'min:1', new StockLimit($item)], // 出庫数の上限は在庫数まで　
        ];
    }
}
