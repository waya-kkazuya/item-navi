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
        // Vue側の命名規則であることに注意
        return [
            // 'image1' => [], // 正方形画像 画像名の命名規則にしたがって制限をかける、何文字以内
            'imageFile' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'name' => ['required', 'min:3' ,'max:20'],
            'categoryId' => ['required', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0', 'max:100'],
            'unitId' => ['required', 'exists:units,id'] ,
            'minimumStock' =>  ['integer', 'min:0', 'max:50'],
            'notification' => ['required', 'boolean'],
            'usageStatusId' => ['required', 'exists:usage_statuses,id'],
            'endUser' => ['nullable','max:10'],
            'locationOfUseId' => ['required', 'exists:locations,id'],
            'storageLocationId' => ['required', 'exists:locations,id'],
            'acquisitionMethodId' => ['required', 'exists:acquisition_methods,id'],
            'acquisitionSource' => ['min:1', 'max:20'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000'],
            'dateOfAcquisition' => ['required', 'date'],
            'manufacturer' => ['nullable', 'max:20'],
            'productNumber' => ['nullable', 'max:30'],
            'remarks' => ['nullable', 'max:500'],
            // 'inspectionSchedule' => ['nullable','date'],
            // 'disposalSchedule' => ['nullable', 'date'],
            // 'qrcode'
        ];
    }
}
