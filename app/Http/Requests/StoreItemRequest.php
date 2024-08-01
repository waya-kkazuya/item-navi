<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
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
            // 'image1' => [], // 正方形画像 画像名の命名規則にしたがって制限をかける、何文字以内
            // 'image2' => [], // 2:1にトリミングした画像
            // 'image3' => [],
            'name' => ['required', 'min:3' ,'max:20'],
            'category_id' => ['required', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0', 'max:100'],
            'minimum_stock' =>  ['integer', 'min:0', 'max:50'],
            'usage_status' => ['required', 'exists::usage_statuses,id'],
            'end_user' => ['min:1','max:10'],
            'location_of_use' => ['required', 'exists:locations,id'],
            'storage_location' => ['required', 'exists:locations,id'],
            'acquisition_method' => ['required', 'exists:acquisition_methods,id'],
            'acquisition_source' => ['min:1', 'max:20'],
            'date_of_acquisition' => ['required', 'date'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000'],
            'manufacturer' => ['min:1','max:20'],
            'product_number' => ['min:1', 'max:30'],
            'remarks' => ['min:1', 'max:500'],
            'inspection_schedule' => ['date'],
            'disposal_schedule' => ['date'],
            // 'qrcode_path'
        ];
    }
}
