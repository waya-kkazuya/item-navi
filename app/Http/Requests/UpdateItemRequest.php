<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
        // Vue側の命名規則であることに注意
        return [
            // 'image1' => ['nullable'], // 正方形画像 画像名の命名規則にしたがって制限をかける、何文字以内
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
            'name' => ['required', 'min:1' ,'max:20'],
            'category_id' => ['required', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0', 'max:200'],
            'unit_id' => ['required', 'exists:units,id'] ,
            'minimum_stock' =>  ['nullable','integer', 'min:0', 'max:50'],
            'notification' => ['required', 'boolean'],
            'usage_status_id' => ['required', 'exists:usage_statuses,id'],
            'end_user' => ['nullable','max:10'],
            'location_of_use_id' => ['required', 'exists:locations,id'],
            'storage_location_id' => ['required', 'exists:locations,id'],
            'acquisition_method_id' => ['required', 'exists:acquisition_methods,id'],
            'acquisition_source' => ['min:1', 'max:20'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000'],
            'date_of_acquisition' => ['required', 'date'],
            'manufacturer' => ['nullable', 'max:20'],
            'product_number' => ['nullable', 'max:30'],
            'remarks' => ['nullable', 'max:500'],
            
            'inspection_scheduled_date' => [
                'date',
                'nullable',
                function ($attribute, $value, $fail) {
                    $dateOfAcquisition = Carbon::parse(request()->input('dateOfAcquisition'));
                    $inspectionSchedule = Carbon::parse($value);
                    if ($inspectionSchedule->lt($dateOfAcquisition)) {
                        $fail('点検予定日は取得年月日以降の日付を入力してください');
                    }                    
                    if ($inspectionSchedule->gt($dateOfAcquisition->addYears(3))) {
                        $fail('点検予定日は取得年月日から3年以内の日付を入力してください');
                    }
                },
            ],
            
            'disposal_scheduled_date' => [
                'date',
                'nullable',
                function ($attribute, $value, $fail) {
                    $dateOfAcquisition = Carbon::parse(request()->input('dateOfAcquisition'));
                    $disposalSchedule = Carbon::parse($value);
                    if ($disposalSchedule->lt($dateOfAcquisition)) {
                        $fail('廃棄予定日は取得年月日以降の日付を入力してください');
                    }                    
                    if ($disposalSchedule->gt($dateOfAcquisition->addYears(3))) {
                        $fail('廃棄予定日は取得年月日から3年以内の日付を入力してください');
                    }
                },
            ],

            // 編集理由のバリデーション
            'edit_reeason_id' => ['required', 'exists:edit_reasons,id'],
            'edit_reason_text' => ['nullable', 'max:200'],
        ];
    }
}
