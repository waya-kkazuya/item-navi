<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
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
            'name' => ['required', 'max:255'],
            // 'name' => ['required', 'unique:items', 'max:255'],
            'category_id' => ['required'],
            // 'image_path1' => [],
            // 'image_path2' => [],
            // 'image_path3' => [],
            'stocks' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['integer', 'min:0'],
            // 'usage_status' => ['required'], // stringで定義されているがエラーになる可能性がある
            'end_user' => ['max:10'],
            // 'location_of_use' => ['required'],
            // 'storage_location' => ['required'],
            'acquisition_category' => ['required'],
            'where_to_buy' => ['max:20'],
            'price' => ['required', 'integer', 'min:0'],
            // 'manufacturer' => ['max:20'],
            // 'product_number' => ['max:100'], //文字列数字のみとは限らない、そして長い可能性
            // 'date_of_acquisition' => ['date'],
            // 'inspection_schedule' => ['date'],
            // 'disposal_schedule' => ['date'],
            // 'remarks' => ['max:1000'],
            // 'qrcode_path'
        ];
    }
}
