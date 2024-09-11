<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncreaseStockRequest extends FormRequest
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
        // $item = $this->route('item'); // ルートパラメータからアイテムIDを取得
        // Log::info($item);

        return [
            // 'item_id' => ['required', 'exists:items,id'],
            'transaction_type' => ['required', 'in:入庫'],
            'transaction_date' => ['required', 'date'], // 1ヵ月前まで
            'operator_name' => ['required', 'max:10'] ,
            'quantity' => ['required', 'integer',  'min:1', 'max:100'],
        ];
    }
}
