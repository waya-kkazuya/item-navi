<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\StockLimit;
use Illuminate\Support\Facades\Log;

class DecreaseStockRequest extends FormRequest
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

        return [
            'transaction_type' => ['required', 'in:出庫'],
            'operator_name' => ['required', 'max:10'] ,
            'quantity' => ['required', 'integer',  'min:1', new StockLimit($item)], // 出庫数の上限は在庫数まで　
        ];
    }
}
