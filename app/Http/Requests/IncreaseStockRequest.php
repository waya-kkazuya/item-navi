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
        return [
            'transaction_type' => ['required', 'in:入庫'],
            'operator_name'    => ['required', 'max:10'],
            'quantity'         => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }
}
